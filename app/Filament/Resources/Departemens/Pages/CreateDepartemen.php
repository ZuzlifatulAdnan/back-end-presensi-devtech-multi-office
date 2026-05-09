<?php

namespace App\Filament\Resources\Departemens\Pages;

use App\Filament\Resources\Departemens\DepartemenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartemen extends CreateRecord
{
    protected static string $resource = DepartemenResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
