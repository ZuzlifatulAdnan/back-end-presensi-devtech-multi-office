<?php

namespace App\Filament\Resources\ShiftKerjas\Pages;

use App\Filament\Resources\ShiftKerjas\ShiftKerjaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShiftKerja extends CreateRecord
{
    protected static string $resource = ShiftKerjaResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
