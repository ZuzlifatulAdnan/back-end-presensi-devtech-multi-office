<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Location Settings')
                    ->description('Configure GPS location validation for attendance')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('-6.200000')
                                    ->helperText('Office GPS latitude coordinate'),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('106.816666')
                                    ->helperText('Office GPS longitude coordinate'),

                                TextInput::make('radius_km')
                                    ->label('Radius (km)')
                                    ->required()
                                    ->numeric()
                                    ->default(0.5)
                                    ->step(0.1)
                                    ->minValue(0.1)
                                    ->maxValue(10)
                                    ->helperText('Allowed check-in radius'),
                            ]),

                        Select::make('attendance_type')
                            ->label('Attendance Method')
                            ->required()
                            ->options([
                                'location_based_only' => 'Location Based Only (GPS)',
                                'face_recognition_only' => 'Face Recognition Only',
                                'hybrid' => 'Hybrid (GPS + Face Recognition)',
                            ])
                            ->default('location_based_only')
                            ->helperText('Choose how employees check in/out')
                            ->native(false),
                    ]),
            ]);
    }
}
