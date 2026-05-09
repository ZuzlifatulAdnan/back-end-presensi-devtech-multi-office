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
                Section::make('Informasi Shift')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Shift')
                            ->required()
                            ->placeholder('Contoh: Shift Pagi, Shift Malam')
                            ->maxLength(255),

                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label('Jam Mulai')
                                    ->required()
                                    ->seconds(false),

                                TimePicker::make('end_time')
                                    ->label('Jam Selesai')
                                    ->required()
                                    ->seconds(false),
                            ]),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi opsional tentang shift ini')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengaturan Shift')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Checkbox::make('is_cross_day')
                                    ->label('Lewat Tengah Malam')
                                    ->helperText('Centang jika shift ini melewati tengah malam (misal: 23:00 - 07:00)')
                                    ->default(false),

                                TextInput::make('grace_period_minutes')
                                    ->label('Masa Toleransi (menit)')
                                    ->helperText('Toleransi keterlambatan dalam menit')
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(0)
                                    ->maxValue(60)
                                    ->required(),

                                Checkbox::make('is_active')
                                    ->label('Aktif')
                                    ->helperText('Hanya shift aktif yang dapat ditugaskan ke pegawai')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
