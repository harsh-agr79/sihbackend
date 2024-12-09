<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Tables\Actions\Action;

class CourseResource extends Resource {
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form( Form $form ): Form {
        return $form
        ->schema( [
        
        ] );
    }

    public static function table( Table $table ): Table {
        return $table
        ->columns( [
            Tables\Columns\TextColumn::make( 'title' )
            ->label( 'Title' )
            ->sortable()
            ->searchable(),
            Tables\Columns\TextColumn::make( 'mentor.name' )
            ->label( 'Mentor' ),
            Tables\Columns\BooleanColumn::make( 'verified' )
            ->label( 'Verified' )
            ->trueIcon( 'heroicon-s-check-circle' )
            ->falseIcon( 'heroicon-s-x-circle' ),
        ] )
        ->filters( [
            //
        ] )
        ->actions( [
            Tables\Actions\ViewAction::make(),
            Action::make('toggleVerified')
                ->label(fn ($record) => $record->verified ? 'Unverify' : 'Verify')
                ->icon(fn ($record) => $record->verified ? 'heroicon-s-x-circle' : 'heroicon-s-check-circle')
                ->color(fn ($record) => $record->verified ? 'danger' : 'success')
                ->action(function ($record) {
                    $record->update(['verified' => !$record->verified]);
                })
                ->requiresConfirmation()
                ->modalHeading(fn ($record) => $record->verified ? 'Unverify Course' : 'Verify Course')
                ->modalSubheading('Are you sure you want to update the verified status?')
                ->successNotificationTitle(fn ($record) => $record->verified ? 'Course Unverified' : 'Course Verified'),
           
        ] )
        ->bulkActions( [
            Tables\Actions\BulkActionGroup::make( [
                
            ] ),
        ] )->headerActions([]);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function canCreate(): bool
        {
            return false;
        }


    public static function infolist( \Filament\Infolists\Infolist $infolist ): \Filament\Infolists\Infolist {
        return $infolist
        ->schema( [
            TextEntry::make( 'title' )
            ->label( 'Course Title' ),
            TextEntry::make( 'description' )
            ->label( 'Description' )
            ->columnSpanFull(),
            TextEntry::make( 'mentor.name' )
            ->label( 'Mentor Name' ),
            TextEntry::make( 'verified' )
            ->label( 'Verified' )
            ->formatStateUsing( fn ( $state ) => $state ? 'Yes' : 'No' ),

            RepeatableEntry::make( 'moduleGroups' )
            ->label( 'Module Groups' )
            ->grid( 2 )
            ->columnSpanFull()
            ->schema( [
                TextEntry::make( 'title' )
                ->label( 'Group Title' ),
                RepeatableEntry::make( 'modules' )
                ->label( 'Modules' )
                ->schema( [
                    TextEntry::make( 'title' )
                    ->label( 'Module Title' ),
                    RepeatableEntry::make( 'assignmentsQuizzes' )
                    ->label( 'Assignments/Quizzes' )
                    ->schema( [
                        TextEntry::make( 'title' )
                        ->label( 'Title' ),
                        TextEntry::make( 'type' )
                        ->label( 'Type' ),
                        TextEntry::make( 'description' )
                        ->label( 'Description' ),
                        TextEntry::make( 'due_date' )
                        ->label( 'Due Date' ),
                    ] ),
                ] ),
            ] ),
            RepeatableEntry::make( 'ungroupedModules' )
            ->label( 'Ungrouped Modules' )
            ->grid( 2 )
            ->columnSpanFull()
            ->schema( [
                TextEntry::make( 'title' )
                ->label( 'Module Title' ),
                RepeatableEntry::make( 'assignmentsQuizzes' )
                ->label( 'Assignments/Quizzes' )
                ->schema( [
                    TextEntry::make( 'title' )
                    ->label( 'Title' ),
                    TextEntry::make( 'type' )
                    ->label( 'Type' ),
                    TextEntry::make( 'description' )
                    ->label( 'Description' ),
                    TextEntry::make( 'due_date' )
                    ->label( 'Due Date' ),
                ] ),
            ] ),
        ] );
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListCourses::route( '/' ),
            // 'create' => Pages\CreateCourse::route( '/create' ),
            //'edit' => Pages\EditCourse::route( '/{record}/edit' ),
        ];
    }
}
