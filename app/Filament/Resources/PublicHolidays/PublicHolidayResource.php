<?php

namespace App\Filament\Resources\PublicHolidays;

use App\Filament\Resources\PublicHolidays\Pages\CreatePublicHoliday;
use App\Filament\Resources\PublicHolidays\Pages\EditPublicHoliday;
use App\Filament\Resources\PublicHolidays\Pages\ListPublicHolidays;
use App\Filament\Resources\PublicHolidays\Schemas\PublicHolidayForm;
use App\Filament\Resources\PublicHolidays\Tables\PublicHolidaysTable;
use App\Models\Holiday;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PublicHolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Public Holidays';

    protected static ?string $pluralLabel = 'Public Holidays';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereIn('type', [Holiday::TYPE_NATIONAL, Holiday::TYPE_COMPANY]);
    }

    public static function form(Schema $schema): Schema
    {
        return PublicHolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicHolidaysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicHolidays::route('/'),
            'create' => CreatePublicHoliday::route('/create'),
            'edit' => EditPublicHoliday::route('/{record}/edit'),
        ];
    }
}
