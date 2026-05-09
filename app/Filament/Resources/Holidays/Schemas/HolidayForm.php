<?php

namespace App\Filament\Resources\Holidays\Schemas;

use App\Models\Holiday;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Holiday Information')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->native(false)
                            ->unique(table: 'holidays', column: 'date', ignoreRecord: true)
                            ->rules([
                                fn ($record) => $record
                                    ? Rule::unique('holidays', 'date')->ignore($record->id)
                                    : Rule::unique('holidays', 'date'),
                            ]),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Independence Day'),

                        Select::make('type')
                            ->label('Type')
                            ->required()
                            ->options([
                                Holiday::TYPE_NATIONAL => 'National',
                                Holiday::TYPE_COMPANY => 'Company',
                                Holiday::TYPE_WEEKEND => 'Weekend',
                            ])
                            ->default(Holiday::TYPE_NATIONAL),

                        Toggle::make('is_official')
                            ->label('Official Holiday')
                            ->default(false)
                            ->helperText('Check if this is an official government holiday'),
                    ])
                    ->columns(2),
            ]);
    }
}
