<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * Mutate the record data to include module groups, modules, and assignments.
     *
     * @param array $data
     * @return array
     */
    protected function mutateRecordDataBeforeFill(array $data): array
    {
        $data['module_groups'] = $this->record->moduleGroups()
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
    }

    /**
     * Define the schema for the verification toggle.
     *
     * @return array
     */
    protected function getFormSchema(): array
    {
        return [
            Toggle::make('verified')
                ->label('Verified')
                ->helperText('Toggle to verify this course.')
                ->default($this->record->verified)
                ->afterStateUpdated(fn (bool $state) => $this->record->update(['verified' => $state]))
                ->live(),
        ];
    }

    /**
     * Return the record title for display.
     *
     * @return string
     */
    public function getRecordTitle(): string
    {
        return $this->record->title;
    }
}
