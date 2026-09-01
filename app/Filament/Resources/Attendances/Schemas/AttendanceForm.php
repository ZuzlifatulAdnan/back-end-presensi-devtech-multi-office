<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('user_id')
                    ->label('Pegawai')
                    ->options(User::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->rule(fn (?Attendance $record, Get $get): Unique => Rule::unique('attendances', 'date')
                        ->where('user_id', $get('user_id'))
                        ->ignore($record))
                    ->validationMessages([
                        'unique' => 'Pegawai ini sudah memiliki data absensi pada tanggal tersebut.',
                    ]),
                TimePicker::make('time_in')
                    ->label('Jam Masuk')
                    ->required()
                    ->seconds(false),
                TimePicker::make('time_out')
                    ->label('Jam Keluar')
                    ->seconds(false),
                TextInput::make('latlon_in')
                    ->label('Lokasi Masuk (Lat, Lon)')
                    ->placeholder('Contoh: -6.2088, 106.8456')
                    ->required(),
                TextInput::make('latlon_out')
                    ->label('Lokasi Keluar (Lat, Lon)')
                    ->placeholder('Contoh: -6.2088, 106.8456'),
                Select::make('work_mode')
                    ->options([
                        'wfo' => 'WFO',
                        'wfh' => 'WFH',
                        'wfa' => 'WFA',
                    ])
                    ->required(),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Kantor / Lokasi')
                    ->searchable()
                    ->preload()
                    ->helperText('Kosongkan untuk presensi WFH/WFA.'),
                Textarea::make('notes_in')
                    ->label('Catatan Aktivitas (Masuk)')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Textarea::make('notes_out')
                    ->label('Catatan Aktivitas (Pulang)')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
