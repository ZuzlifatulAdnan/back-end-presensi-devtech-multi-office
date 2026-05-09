<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Type Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('quota_days')
                            ->label('Quota Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Enter the number of days allowed per year. Use 0 for unlimited.'),

                        Toggle::make('is_paid')
                            ->label('Is Paid Leave')
                            ->default(true)
                            ->helperText('Check if this leave type is paid'),
                    ])
                    ->columns(2),
            ]);
    }
}
