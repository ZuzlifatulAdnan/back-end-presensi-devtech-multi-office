<?php

namespace App\Filament\Resources\Leaves\Schemas;

use App\Support\WorkdayCalculator;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Cuti')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Pegawai')
                            ->required()
                            ->searchable()
                            ->relationship('employee', 'name')
                            ->preload()
                            ->default(auth()->id()),

                        Select::make('leave_type_id')
                            ->label('Jenis Cuti')
                            ->required()
                            ->searchable()
                            ->relationship('leaveType', 'name')
                            ->preload(),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotalDays($get, $set);
                            }),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotalDays($get, $set);
                            }),

                        Select::make('total_days')
                            ->label('Total Hari')
                            ->disabled()
                            ->dehydrated()
                            ->default(1),

                        Textarea::make('reason')
                            ->label('Alasan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Dokumen Pendukung')
                    ->description('Surat dokter, surat tugas, atau bukti lain (JPG, PNG, WEBP, PDF - maks 5 MB).')
                    ->schema([
                        FileUpload::make('attachment_url')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('leave-attachments')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Status Persetujuan')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('pending')
                            ->disabled(fn ($record) => $record === null)
                            ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),
                    ])
                    ->visible(fn ($record) => $record !== null && (auth()->user()->role === 'admin' || auth()->user()->role === 'hr')),
            ]);
    }

    protected static function calculateTotalDays($get, $set): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays($start, $end);
            $set('total_days', $totalDays);
        }
    }
}
