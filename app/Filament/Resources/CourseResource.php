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
use Filament\Tables\Actions\ViewAction;

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
                ->inline(false)
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
        ->actions([
            ViewAction::make()
                ->label('View')
                ->modalHeading(fn ($record) => "Details for {$record->title}")
                ->modalWidth('xl')
                ->view('filament.resources.course-resource.view-course-modal')
                ->mutateRecordDataUsing(function (array $data): array {
                    $data['module_groups'] = Course::find($data['id']) // Retrieve the course and its relationships
                        ->moduleGroups()
                        ->with(['modules.assignmentsQuizzes'])
                        ->get()
                        ->map(function ($group) {
                            return [
                                'id' => $group->id,
                                'title' => $group->title,
                                'modules' => $group->modules->map(function ($module) {
                                    return [
                                        'id' => $module->id,
                                        'title' => $module->title,
                                        'assignments_quizzes' => $module->assignmentsQuizzes->map(function ($assignment) {
                                            return [
                                                'id' => $assignment->id,
                                                'type' => $assignment->type,
                                                'title' => $assignment->title,
                                                'description' => $assignment->description,
                                                'content' => $assignment->content,
                                                'due_date' => $assignment->due_date,
                                            ];
                                        }),
                                    ];
                                }),
                            ];
                        });

                    return $data;
                }),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
