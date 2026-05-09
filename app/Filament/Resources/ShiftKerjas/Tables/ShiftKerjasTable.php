<?php

namespace App\Filament\Resources\ShiftKerjas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ShiftKerjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Shift Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->iconPosition(IconPosition::Before),

                TextColumn::make('end_time')
                    ->label('End Time')
                    ->time('H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->iconPosition(IconPosition::Before),

                IconColumn::make('is_cross_day')
                    ->label('Cross Midnight')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('grace_period_minutes')
                    ->label('Grace Period')
                    ->suffix(' min')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('users_count')
                    ->label('Employees')
                    ->counts('users')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Shifts')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),

                TernaryFilter::make('is_cross_day')
                    ->label('Cross Midnight')
                    ->placeholder('All Shifts')
                    ->trueLabel('Cross Midnight')
                    ->falseLabel('Same Day'),
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
            ->defaultSort('name', 'asc');
    }
}
