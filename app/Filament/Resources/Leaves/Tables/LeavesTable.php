<?php

namespace App\Filament\Resources\Leaves\Tables;

use App\Exceptions\LeaveException;
use App\Models\Leave;
use App\Services\LeaveService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class LeavesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('leaveType.name')
                    ->label('Jenis Cuti')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_days')
                    ->label('Total Hari')
                    ->sortable(),

                IconColumn::make('attachment_url')
                    ->label('Lampiran')
                    ->icon(fn ($record) => $record->attachment_url ? 'heroicon-o-paper-clip' : null)
                    ->color('primary')
                    ->url(fn ($record) => $record->attachment_url ? Storage::url($record->attachment_url) : null)
                    ->openUrlInNewTab()
                    ->alignCenter()
                    ->tooltip(fn ($record) => $record->attachment_url ? 'Lihat Lampiran' : 'Tidak Ada Lampiran'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('approved_at')
                    ->label('Waktu Persetujuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable(),

                SelectFilter::make('leave_type_id')
                    ->label('Jenis Cuti')
                    ->relationship('leaveType', 'name')
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Dari'),
                        DatePicker::make('end_date')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->where('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->where('end_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View'),

                EditAction::make()
                    ->label('Edit')
                    ->visible(fn (Leave $record) => $record->status === Leave::STATUS_PENDING),

                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Leave $record) => $record->status === Leave::STATUS_PENDING && in_array(auth()->user()->role, ['admin', 'hr'], true))
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan')
                    ->modalDescription(fn (Leave $record) => 'Pegawai: '.$record->employee->name.' | Jenis: '.$record->leaveType->name.' | Tanggal: '.$record->start_date->format('d/m/Y').' - '.$record->end_date->format('d/m/Y'))
                    ->action(function (Leave $record, LeaveService $leaveService) {
                        try {
                            $leaveService->approve($record, auth()->user());

                            Notification::make()
                                ->title('Pengajuan disetujui')
                                ->success()
                                ->send();
                        } catch (LeaveException $exception) {
                            Notification::make()
                                ->title('Pengajuan tidak dapat disetujui')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Leave $record) => $record->status === Leave::STATUS_PENDING && in_array(auth()->user()->role, ['admin', 'hr'], true))
                    ->form([
                        Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->required(),
                    ])
                    ->modalHeading('Tolak Pengajuan')
                    ->modalDescription(fn (Leave $record) => 'Pegawai: '.$record->employee->name.' | Jenis: '.$record->leaveType->name)
                    ->action(function (Leave $record, array $data, LeaveService $leaveService) {
                        try {
                            $leaveService->reject($record, auth()->user(), $data['notes']);

                            Notification::make()
                                ->title('Pengajuan ditolak')
                                ->success()
                                ->send();
                        } catch (LeaveException $exception) {
                            Notification::make()
                                ->title('Pengajuan tidak dapat ditolak')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('revoke')
                    ->label('Batalkan Persetujuan')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (Leave $record) => $record->status === Leave::STATUS_APPROVED && in_array(auth()->user()->role, ['admin', 'hr'], true))
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Persetujuan')
                    ->modalDescription('Kuota cuti yang sudah terpakai akan dikembalikan.')
                    ->action(function (Leave $record, LeaveService $leaveService) {
                        try {
                            $leaveService->revokeApproval($record);

                            Notification::make()
                                ->title('Persetujuan dibatalkan dan kuota dikembalikan')
                                ->success()
                                ->send();
                        } catch (LeaveException $exception) {
                            Notification::make()
                                ->title('Gagal membatalkan persetujuan')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'admin' || auth()->user()->role === 'hr'),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['employee', 'leaveType', 'approver']))
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
