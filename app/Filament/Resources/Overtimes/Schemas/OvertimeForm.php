<?php

namespace App\Filament\Resources\Overtimes\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OvertimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lembur')
                    ->schema([
                        Select::make('user_id')
                            ->label('Karyawan')
                            ->relationship('user', 'name')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->required(),

                        DatePicker::make('date')
                            ->label('Tanggal Lembur')
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required(),

                        Textarea::make('reason')
                            ->label('Alasan Lembur')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Dokumen Pendukung')
                    ->schema([
                        FileUpload::make('document')
                            ->label('Dokumen PDF (Surat Tugas, dll)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('overtime-documents')
                            ->visibility('private')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Status Persetujuan')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required()
                            ->reactive(),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan (opsional)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($operation) => $operation === 'edit'),
            ]);
    }
}
