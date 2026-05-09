<?php

namespace App\Filament\Resources\LeaveBalances\Tables;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeaveBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),

                TextColumn::make('quota_days')
                    ->label('Quota Days')
                    ->sortable(),

                TextColumn::make('used_days')
                    ->label('Used Days')
                    ->sortable(),

                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->sortable()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 3 ? 'warning' : 'success')),

                // TextColumn::make('carry_over_days')
                //     ->label('Carry Over Days')
                //     ->sortable(),

                TextColumn::make('last_updated')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'name')
                    ->searchable(),

                SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship('leaveType', 'name')
                    ->searchable(),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $currentYear = now()->year;

                        return collect(range($currentYear - 2, $currentYear + 1))
                            ->mapWithKeys(fn ($year) => [$year => $year]);
                    }),
            ])
            ->recordActions([
                // EditAction::make()
                //     ->label('Edit')
                //     ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),
            ])
            ->headerActions([
                // Action::make('generate_balances')
                //     ->label('Generate Leave Balances')
                //     ->icon('heroicon-o-sparkles')
                //     ->color('success')
                //     ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr')
                //     ->requiresConfirmation()
                //     ->modalHeading('Generate Leave Balances')
                //     ->modalDescription('This will generate leave balances for all employees for the current year. Existing balances will be updated with carry-over from the previous year.')
                //     ->action(function () {
                //         $users = User::all();
                //         $leaveTypes = LeaveType::all();
                //         $currentYear = now()->year;
                //         $previousYear = $currentYear - 1;

                //         $created = 0;
                //         $updated = 0;

                //         foreach ($users as $user) {
                //             foreach ($leaveTypes as $leaveType) {
                //                 // Check if balance already exists
                //                 $existingBalance = LeaveBalance::where('employee_id', $user->id)
                //                     ->where('leave_type_id', $leaveType->id)
                //                     ->where('year', $currentYear)
                //                     ->first();

                //                 // Get previous year balance for carry over
                //                 $previousBalance = LeaveBalance::where('employee_id', $user->id)
                //                     ->where('leave_type_id', $leaveType->id)
                //                     ->where('year', $previousYear)
                //                     ->first();

                //                 $carryOverDays = $previousBalance ? max(0, $previousBalance->remaining_days) : 0;

                //                 if ($existingBalance) {
                //                     // Update existing balance
                //                     $existingBalance->update([
                //                         'quota_days' => $leaveType->quota_days,
                //                         'carry_over_days' => $carryOverDays,
                //                         'remaining_days' => $leaveType->quota_days + $carryOverDays - $existingBalance->used_days,
                //                         'last_updated' => now(),
                //                     ]);
                //                     $updated++;
                //                 } else {
                //                     // Create new balance
                //                     LeaveBalance::create([
                //                         'employee_id' => $user->id,
                //                         'leave_type_id' => $leaveType->id,
                //                         'year' => $currentYear,
                //                         'quota_days' => $leaveType->quota_days,
                //                         'used_days' => 0,
                //                         'remaining_days' => $leaveType->quota_days + $carryOverDays,
                //                         'carry_over_days' => $carryOverDays,
                //                         'last_updated' => now(),
                //                     ]);
                //                     $created++;
                //                 }
                //             }
                //         }

                //         \Filament\Notifications\Notification::make()
                //             ->title('Leave balances generated successfully')
                //             ->success()
                //             ->body("Created: {$created}, Updated: {$updated}")
                //             ->send();
                //     }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),
                ]),
            ])
            ->defaultSort('employee.name', 'asc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
