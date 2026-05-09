<?php

namespace App\Filament\Resources\Overtimes\Pages;

use App\Filament\Resources\Overtimes\OvertimeResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Messaging\CloudMessage;

class EditOvertime extends EditRecord
{
    protected static string $resource = OvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalStatus = $this->record->status;

        // Jika status berubah dari pending ke approved/rejected
        if ($originalStatus === 'pending' && in_array($data['status'], ['approved', 'rejected'])) {
            $data['approved_at'] = now();
            $data['approved_by'] = Auth::id();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $originalStatus = $this->record->getOriginal('status');
        $newStatus = $this->record->status;
        $statusText = ($newStatus === 'approved')
            ? 'disetujui'
            : (($newStatus === 'rejected')
                ? 'ditolak'
                : 'Menunggu');
        // Kirim notifikasi Firebase jika status berubah
        if ($originalStatus !== $newStatus && in_array($newStatus, ['approved', 'rejected'])) {
            // $this->sendFirebaseNotification($newStatus);
            $user = User::find($this->record->user_id);
            $token = $user->fcm_token;

            // Kirim notifikasi ke perangkat Android
            $messaging = app('firebase.messaging');
            $notification = Notification::create('Status Izin', "Lembur untuk {$this->record->user->name} telah {$statusText}.");

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            $messaging->send($message);
        }

        // Tampilkan notifikasi di dashboard

        Notification::make()
            ->title('Lembur '.ucfirst($statusText))
            ->body("Lembur untuk {$this->record->user->name} telah {$statusText}.")
            ->success()
            ->send();
    }
}
