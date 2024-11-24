<?php

namespace App\Filament\Resources\SubdomainResource\Pages;

use App\Filament\Resources\SubdomainResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubdomain extends CreateRecord
{
    protected static string $resource = SubdomainResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
