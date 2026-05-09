<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        $currentYear = now()->year;

        // Get all active leave types
        $leaveTypes = LeaveType::all();

        // Create leave balance for each leave type
        foreach ($leaveTypes as $leaveType) {
            LeaveBalance::create([
                'employee_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => $currentYear,
                'quota_days' => $leaveType->quota_days,
                'used_days' => 0,
                'remaining_days' => $leaveType->quota_days,
                'carry_over_days' => 0,
                'last_updated' => now(),
            ]);
        }
    }

    public function getFormActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Batal')
                ->color('secondary')
                ->outlined()
                ->url($this->getResource()::getUrl('index')),
            Action::make('save')
                ->label('Simpan')
                ->submit('save')
                ->color('primary')
                ->action(function () {
                    $this->save();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}
