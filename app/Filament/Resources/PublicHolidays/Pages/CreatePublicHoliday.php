<?php

namespace App\Filament\Resources\PublicHolidays\Pages;

use App\Filament\Resources\PublicHolidays\PublicHolidayResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicHoliday extends CreateRecord
{
    protected static string $resource = PublicHolidayResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
