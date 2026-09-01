<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kantor')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('radius_km')
                    ->label('Radius')
                    ->numeric()
                    ->sortable()
                    ->suffix(' km'),
                TextColumn::make('attendance_type')
                    ->label('Metode Presensi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'location_based_only' => 'Berbasis Lokasi (GPS)',
                        'face_recognition_only' => 'Pengenalan Wajah',
                        'hybrid' => 'Hybrid (GPS + Wajah)',
                        default => $state,
                    }),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Lokasi')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
