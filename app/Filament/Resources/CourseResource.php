<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form schema.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required(),
                Forms\Components\Toggle::make('verified')
                    ->label('Verified')
                    ->inline(false)
                    ->default(false),
            ]);
    }

    /**
     * Define the table schema.
     */
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
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query) => $query->where('verified', true))
                    ->label('Verified'),
                Tables\Filters\Filter::make('unverified')
                    ->query(fn (Builder $query) => $query->where('verified', false))
                    ->label('Unverified'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Define relations.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Define pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}
