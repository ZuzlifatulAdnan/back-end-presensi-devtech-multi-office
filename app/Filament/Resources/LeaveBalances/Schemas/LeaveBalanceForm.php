<?php

namespace App\Filament\Resources\LeaveBalances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveBalanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Balance Information')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->required()
                            ->searchable()
                            ->relationship('employee', 'name')
                            ->preload()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->required()
                            ->searchable()
                            ->relationship('leaveType', 'name')
                            ->preload()
                            ->disabled(fn ($record) => $record !== null),

                        TextInput::make('year')
                            ->label('Year')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->disabled(fn ($record) => $record !== null),

                        TextInput::make('quota_days')
                            ->label('Quota Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $usedDays = $get('used_days') ?? 0;
                                $carryOverDays = $get('carry_over_days') ?? 0;
                                $set('remaining_days', $state + $carryOverDays - $usedDays);
                            }),

                        TextInput::make('used_days')
                            ->label('Used Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->disabled(),

                        // TextInput::make('carry_over_days')
                        //     ->label('Carry Over Days')
                        //     ->required()
                        //     ->numeric()
                        //     ->minValue(0)
                        //     ->default(0)
                        //     ->reactive()
                        //     ->afterStateUpdated(function ($state, $set, $get) {
                        //         $quotaDays = $get('quota_days') ?? 0;
                        //         $usedDays = $get('used_days') ?? 0;
                        //         $set('remaining_days', $quotaDays + $state - $usedDays);
                        //     }),

                        TextInput::make('remaining_days')
                            ->label('Remaining Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),
            ]);
    }
}
