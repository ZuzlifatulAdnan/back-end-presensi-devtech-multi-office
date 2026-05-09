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
                Section::make('Leave Information')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->required()
                            ->searchable()
                            ->relationship('employee', 'name')
                            ->preload()
                            ->default(auth()->id()),

                        Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->required()
                            ->searchable()
                            ->relationship('leaveType', 'name')
                            ->preload(),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotalDays($get, $set);
                            }),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotalDays($get, $set);
                            }),

                        Select::make('total_days')
                            ->label('Total Days')
                            ->disabled()
                            ->dehydrated()
                            ->default(1),

                        Textarea::make('reason')
                            ->label('Reason')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Section::make('Supporting Document')
                //     ->schema([
                //         FileUpload::make('attachment_url')
                //             ->label('Attachment')
                //             ->image()
                //             ->directory('leave-attachments')
                //             ->visibility('private')
                //             ->downloadable()
                //             ->openable()
                //             ->columnSpanFull(),
                //     ]),

                Section::make('Approval Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->disabled(fn ($record) => $record === null)
                            ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),

                        Textarea::make('notes')
                            ->label('Notes')
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
