<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;

class ViewCourse extends Page
{
    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.resources.course-resource.pages.view-course';

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function mutateRecordDataBeforeFill(array $data): array
    {
        // Include related data for module groups, modules, and assignments
        $data['module_groups'] = $this->record->moduleGroups()->with(['modules.assignmentsQuizzes'])->get()->map(function ($group) {
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
    }

    protected function getFormSchema(): array
    {
        return [
            Toggle::make('verified')
                ->label('Verified')
                ->inline(false)
                ->helperText('Toggle to verify this course.')
                ->default($this->record->verified)
                ->afterStateUpdated(fn (bool $state) => $this->record->update(['verified' => $state]))
                ->live(),
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);
    }

    public function getRecordTitle(): string
    {
        return $this->record->title;
    }
}
