<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('address')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('radius_km')
                    ->numeric()
                    ->sortable()
                    ->suffix(' km'),
                TextColumn::make('attendance_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'location_based_only' => 'Location Based',
                        'face_recognition_only' => 'Face Recognition',
                        'hybrid' => 'Hybrid',
                        default => $state,
                    }),
            ])
            ->filters([
                //
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
