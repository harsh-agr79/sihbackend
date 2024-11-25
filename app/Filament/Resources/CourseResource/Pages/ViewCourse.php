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
