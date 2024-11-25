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

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('verified')
                    ->label('Verified')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('mentor.name')
                    ->label('Mentor'),
                Tables\Columns\BooleanColumn::make('verified')
                    ->label('Verified')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('title')
                    ->label('Course Title'),
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextEntry::make('mentor.name')
                    ->label('Mentor Name'),
                TextEntry::make('verified')
                    ->label('Verified')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),

                RepeatableEntry::make('moduleGroups')
                    ->label('Module Groups')
                    ->grid(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Group Title'),
                        RepeatableEntry::make('modules')
                            ->label('Modules')
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Module Title'),
                                RepeatableEntry::make('assignmentsQuizzes')
                                    ->label('Assignments/Quizzes')
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('Title'),
                                        TextEntry::make('type')
                                            ->label('Type'),
                                        TextEntry::make('description')
                                            ->label('Description'),
                                        TextEntry::make('due_date')
                                            ->label('Due Date'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
