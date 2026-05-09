<?php

namespace App\Filament\Resources\ShiftKerjas\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShiftKerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shift Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Shift Name')
                            ->required()
                            ->placeholder('e.g., Morning Shift, Night Shift')
                            ->maxLength(255),

                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label('Start Time')
                                    ->required()
                                    ->seconds(false),

                                TimePicker::make('end_time')
                                    ->label('End Time')
                                    ->required()
                                    ->seconds(false),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Optional description about this shift')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Shift Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Checkbox::make('is_cross_day')
                                    ->label('Cross Midnight')
                                    ->helperText('Check if this shift crosses midnight (e.g., 23:00 - 07:00)')
                                    ->default(false),

                                TextInput::make('grace_period_minutes')
                                    ->label('Grace Period (minutes)')
                                    ->helperText('Late tolerance in minutes')
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(0)
                                    ->maxValue(60)
                                    ->required(),

                                Checkbox::make('is_active')
                                    ->label('Active')
                                    ->helperText('Only active shifts can be assigned')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
