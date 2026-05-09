<?php

namespace App\Filament\Resources\Overtimes\Pages;

use App\Filament\Resources\Overtimes\OvertimeResource;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListOvertimes extends ListRecords
{
    protected static string $resource = OvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function exportToPdf()
    {
        try {
            // Get filtered data from the table
            $query = $this->getFilteredTableQuery();
            $overtimes = $query->with(['user', 'approver'])->get();

            // Create PDF using blade view
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('filament.pages.laporan-overtime-pdf', [
                'overtimes' => $overtimes,
                'filters' => [
                    'tanggal_mulai' => now()->startOfMonth()->format('Y-m-d'),
                    'tanggal_selesai' => now()->format('Y-m-d'),
                    'status' => null,
                    'user_id' => null,
                ],
                'exported_at' => now()->format('d/m/Y H:i'),
            ])
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            $filename = 'laporan-overtime-'.now()->format('d-m-Y').'.pdf';

            Notification::make()
                ->title('PDF berhasil diunduh')
                ->success()
                ->send();

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export PDF Gagal')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportToExcel()
    {
        try {
            // Get filtered data from the table
            $query = $this->getFilteredTableQuery();
            $overtimes = $query->with(['user'])->get();

            $csvData = "No,Nama Karyawan,Tanggal,Jam Mulai,Jam Selesai,Alasan,Status,Catatan\n";

            foreach ($overtimes as $index => $overtime) {
                $startTime = $overtime->start_time ?: '-';
                $endTime = $overtime->end_time ?: '-';

                $status = match ($overtime->status) {
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'pending' => 'Pending',
                    default => $overtime->status,
                };

                $csvData .= sprintf(
                    "%d,%s,%s,%s,%s,\"%s\",%s,\"%s\"\n",
                    $index + 1,
                    $overtime->user->name,
                    Carbon::parse($overtime->date)->format('d/m/Y'),
                    $startTime,
                    $endTime,
                    str_replace('"', '""', $overtime->reason),
                    $status,
                    str_replace('"', '""', $overtime->notes ?? '-')
                );
            }

            $filename = 'laporan-overtime-'.now()->format('d-m-Y').'.csv';

            Notification::make()
                ->title('Excel berhasil diunduh')
                ->success()
                ->send();

            return Response::make($csvData, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Excel Gagal')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
