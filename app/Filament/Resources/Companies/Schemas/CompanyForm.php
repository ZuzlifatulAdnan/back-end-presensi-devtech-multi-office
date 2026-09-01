<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kantor')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kantor')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pengaturan Lokasi')
                    ->description('Konfigurasi validasi lokasi GPS untuk presensi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('-6.200000')
                                    ->helperText('Koordinat latitude kantor'),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('106.816666')
                                    ->helperText('Koordinat longitude kantor'),

                                TextInput::make('radius_km')
                                    ->label('Radius (km)')
                                    ->required()
                                    ->numeric()
                                    ->default(0.5)
                                    ->step(0.1)
                                    ->minValue(0.1)
                                    ->maxValue(10)
                                    ->helperText('Radius check-in yang diizinkan'),
                            ]),

                        Toggle::make('is_active')
                            ->label('Lokasi Aktif')
                            ->default(true)
                            ->helperText('Lokasi non-aktif tidak dipakai untuk validasi radius presensi.'),

                        Select::make('attendance_type')
                            ->label('Metode Presensi')
                            ->required()
                            ->options([
                                'location_based_only' => 'Hanya Lokasi (GPS)',
                                'face_recognition_only' => 'Hanya Pengenalan Wajah',
                                'hybrid' => 'Hybrid (GPS + Pengenalan Wajah)',
                            ])
                            ->default('location_based_only')
                            ->helperText('Pilih bagaimana pegawai melakukan absen masuk/pulang')
                            ->native(false),
                    ]),
            ]);
    }
}
