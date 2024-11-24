<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubdomainResource\Pages;
use App\Filament\Resources\SubdomainResource\RelationManagers;
use App\Models\Subdomain;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubdomainResource extends Resource
{
    protected static ?string $model = Subdomain::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('domain_id')
                ->relationship('domain', 'name')
                ->required()
                ->label('Domain'),
            Forms\Components\TextInput::make('name')
                ->required()
                ->unique()
                ->label('Subdomain Name'),
            Forms\Components\Textarea::make('description')->label('Description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain.name')->label('Domain'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created At'),
            ])
            ->filters([
                //
            ])
            ->actions([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubdomains::route('/'),
            'create' => Pages\CreateSubdomain::route('/create'),
            'edit' => Pages\EditSubdomain::route('/{record}/edit'),
        ];
    }
}
