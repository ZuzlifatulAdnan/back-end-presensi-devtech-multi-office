<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => 'data:image/svg+xml;base64,'.base64_encode('
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" style="background-color: #F3F4F6;">
                            <g transform="translate(25, 25)">
                                <path fill="#9CA3AF" fill-rule="evenodd" d="M-5 -10a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM-8.249 4.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 010 6.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
                            </g>
                        </svg>
                    '))
                    ->size(50),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'employee' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'manager' => 'Manajer',
                        'employee' => 'Karyawan',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('work_mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wfo' => 'success',
                        'wfh' => 'warning',
                        'wfa' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->searchable(),
                TextColumn::make('jabatan.name')
                    ->label('Jabatan')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum diset')
                    ->icon('heroicon-o-briefcase'),
                TextColumn::make('departemen.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum diset')
                    ->icon('heroicon-o-building-library'),
                TextColumn::make('shiftKerja.name')
                    ->label('Shift')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum diset')
                    ->icon('heroicon-o-clock'),
                TextColumn::make('company.name')
                    ->label('Lokasi / Kantor')
                    ->badge()
                    ->color('secondary')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Pusat / Semua Cabang')
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manajer',
                        'employee' => 'Karyawan',
                    ]),
                SelectFilter::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('departemen_id')
                    ->label('Departemen')
                    ->relationship('departemen', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('shift_kerja_id')
                    ->label('Shift Kerja')
                    ->relationship('shiftKerja', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('company_id')
                    ->label('Lokasi / Kantor')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
