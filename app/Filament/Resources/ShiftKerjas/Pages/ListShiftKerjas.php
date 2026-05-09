<?php

namespace App\Filament\Resources\ShiftKerjas\Pages;

use App\Filament\Resources\ShiftKerjas\ShiftKerjaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShiftKerjas extends ListRecords
{
    protected static string $resource = ShiftKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
