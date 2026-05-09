<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(User::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->native(false),
                TimePicker::make('time_in')
                    ->label('Check In Time')
                    ->required()
                    ->seconds(false),
                TimePicker::make('time_out')
                    ->label('Check Out Time')
                    ->seconds(false),
                TextInput::make('latlon_in')
                    ->label('Check In Location (Lat, Lon)')
                    ->placeholder('e.g., -6.2088, 106.8456')
                    ->required(),
                TextInput::make('latlon_out')
                    ->label('Check Out Location (Lat, Lon)')
                    ->placeholder('e.g., -6.2088, 106.8456'),
            ]);
    }
}
