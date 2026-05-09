<?php

namespace App\Filament\Resources\ShiftKerjas\Pages;

use App\Filament\Resources\ShiftKerjas\ShiftKerjaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShiftKerja extends EditRecord
{
    protected static string $resource = ShiftKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
