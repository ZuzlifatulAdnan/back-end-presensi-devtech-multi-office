<?php

namespace App\Filament\Resources\Holidays\Tables;

use App\Models\Holiday;
use App\Support\WorkdayCalculator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'info' => Holiday::TYPE_NATIONAL,
                        'warning' => Holiday::TYPE_COMPANY,
                        'gray' => Holiday::TYPE_WEEKEND,
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                IconColumn::make('is_official')
                    ->label('Official')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = DB::table('holidays')
                            ->selectRaw('YEAR(date) as year')
                            ->distinct()
                            ->orderByDesc('year')
                            ->pluck('year', 'year');

                        if ($years->isEmpty()) {
                            $currentYear = now()->year;

                            return [
                                $currentYear - 1 => $currentYear - 1,
                                $currentYear => $currentYear,
                                $currentYear + 1 => $currentYear + 1,
                            ];
                        }

                        return $years;
                    })
                    ->query(function (Builder $query, $state) {
                        if ($state['value'] ?? null) {
                            return $query->whereYear('date', $state['value']);
                        }
                    }),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Holiday::TYPE_NATIONAL => 'National',
                        Holiday::TYPE_COMPANY => 'Company',
                        Holiday::TYPE_WEEKEND => 'Weekend',
                    ]),

                SelectFilter::make('official_only')
                    ->label('Official Only')
                    ->options([
                        '1' => 'Official Holidays Only',
                    ])
                    ->query(fn (Builder $query, $state) => $state['value'] === '1' ? $query->where('is_official', true) : $query),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit'),
            ])
            ->headerActions([
                Action::make('generate_weekends')
                    ->label('Generate Weekends')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->form([
                        TextInput::make('year')
                            ->label('Year')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2100)
                            ->default(now()->year),
                    ])
                    ->modalHeading('Generate Weekend Holidays')
                    ->modalDescription('This will generate all Saturdays and Sundays for the selected year as weekend holidays.')
                    ->action(function (array $data) {
                        $result = WorkdayCalculator::generateWeekendForYear($data['year']);

                        \Filament\Notifications\Notification::make()
                            ->title('Weekends generated successfully')
                            ->success()
                            ->body("Inserted: {$result['inserted']}, Skipped: {$result['skipped']}")
                            ->send();
                    }),

                Action::make('quick_add_national')
                    ->label('Quick Add National Holiday')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->form([
                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->native(false)
                            ->unique(table: 'holidays', column: 'date'),

                        TextInput::make('name')
                            ->label('Holiday Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Independence Day'),
                    ])
                    ->modalHeading('Add National Holiday')
                    ->action(function (array $data) {
                        Holiday::create([
                            'date' => $data['date'],
                            'name' => $data['name'],
                            'type' => Holiday::TYPE_NATIONAL,
                            'is_official' => true,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('National holiday added successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),

                    Action::make('set_official')
                        ->label('Set as Official')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_official' => true]);

                            \Filament\Notifications\Notification::make()
                                ->title('Holidays marked as official')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),

                    Action::make('unset_official')
                        ->label('Unset Official')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_official' => false]);

                            \Filament\Notifications\Notification::make()
                                ->title('Holidays unmarked as official')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
