<?php

namespace App\Filament\Resources\PublicHolidays\Schemas;

use App\Models\Holiday;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PublicHolidayForm
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
                            ->label('Holiday Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Independence Day, Eid al-Fitr'),

                        Select::make('type')
                            ->label('Type')
                            ->required()
                            ->options([
                                Holiday::TYPE_NATIONAL => 'National Holiday',
                                Holiday::TYPE_COMPANY => 'Company Holiday',
                            ])
                            ->default(Holiday::TYPE_NATIONAL)
                            ->helperText('National = government holidays, Company = company-specific holidays'),

                        Toggle::make('is_official')
                            ->label('Official Holiday')
                            ->default(true)
                            ->helperText('Mark as official government/company holiday'),
                    ])
                    ->columns(2),
            ]);
    }
}
