<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateRecordDataBeforeFill(array $data): array
    {
        // Load related module groups, modules, and assignments/quizzes
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
}
