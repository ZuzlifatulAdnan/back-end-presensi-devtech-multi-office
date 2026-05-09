<?php

namespace App\Filament\Resources\ShiftKerjas;

use App\Filament\Resources\ShiftKerjas\Pages\CreateShiftKerja;
use App\Filament\Resources\ShiftKerjas\Pages\EditShiftKerja;
use App\Filament\Resources\ShiftKerjas\Pages\ListShiftKerjas;
use App\Filament\Resources\ShiftKerjas\Schemas\ShiftKerjaForm;
use App\Filament\Resources\ShiftKerjas\Tables\ShiftKerjasTable;
use App\Models\ShiftKerja;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ShiftKerjaResource extends Resource
{
    protected static ?string $model = ShiftKerja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static UnitEnum|string|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Work Shifts';

    public static function form(Schema $schema): Schema
    {
        return ShiftKerjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShiftKerjasTable::configure($table);
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
            'index' => ListShiftKerjas::route('/'),
            'create' => CreateShiftKerja::route('/create'),
            'edit' => EditShiftKerja::route('/{record}/edit'),
        ];
    }
}
