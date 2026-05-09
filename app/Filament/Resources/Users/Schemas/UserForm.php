<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->minLength(8),
                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->maxLength(20),
                Select::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manajer',
                        'employee' => 'Karyawan',
                    ])
                    ->required()
                    ->default('employee'),
                Select::make('work_mode')
                    ->label('Mode Kerja Default')
                    ->options([
                        'wfo' => 'WFO (Work From Office)',
                        'wfh' => 'WFH (Work From Home)',
                        'wfa' => 'WFA (Work From Anywhere)',
                    ])
                    ->required()
                    ->default('wfo')
                    ->helperText('Mode kerja bawaan untuk pegawai ini'),
                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih 1 jabatan untuk karyawan'),
                Select::make('departemen_id')
                    ->label('Departemen')
                    ->relationship('departemen', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih 1 departemen untuk karyawan'),
                Select::make('shift_kerja_id')
                    ->label('Shift Kerja')
                    ->relationship('shiftKerja', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih 1 shift kerja untuk karyawan'),
                Select::make('company_id')
                    ->label('Perusahaan / Lokasi')
                    ->relationship('company', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih lokasi tempat pegawai ini bekerja (Opsional)'),
                FileUpload::make('image_url')
                    ->label('Avatar')
                    ->image()
                    ->imageEditor()
                    ->directory('avatars')
                    ->visibility('public')
                    ->disk('public')
                    ->columnSpanFull(),
                Textarea::make('face_embedding')
                    ->label('Face Embedding Data')
                    ->hidden()
                    ->columnSpanFull(),
                TextInput::make('fcm_token')
                    ->label('FCM Token')
                    ->hidden()
                    ->columnSpanFull(),
            ]);
    }
}
