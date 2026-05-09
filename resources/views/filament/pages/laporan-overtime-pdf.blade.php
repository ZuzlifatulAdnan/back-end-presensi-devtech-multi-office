<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Overtime</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .filter-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }

        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }

        .filter-item strong {
            color: #495057;
        }



        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .reason-text {
            max-width: 150px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .notes-text {
            max-width: 120px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN OVERTIME</h1>
        <p>{{ config('app.name', 'Sistem Absensi') }}</p>
        @if (isset($filters))
            <p>Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->format('d/m/Y') }}</p>
        @endif
    </div>

    @if (isset($filters))
        <div class="filter-info">
            <h3>Filter yang Diterapkan:</h3>
            <div class="filter-item">
                <strong>Periode:</strong> {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->format('d/m/Y') }}
            </div>
            @if ($filters['status'])
                <div class="filter-item">
                    <strong>Status:</strong>
                    @switch($filters['status'])
                        @case('approved')
                            Disetujui
                        @break

                        @case('rejected')
                            Ditolak
                        @break

                        @case('pending')
                            Pending
                        @break

                        @default
                            {{ $filters['status'] }}
                    @endswitch
                </div>
            @endif
            @if ($filters['user_id'])
                <div class="filter-item">
                    <strong>Karyawan:</strong>
                    {{ \App\Models\User::find($filters['user_id'])->name ?? 'Tidak ditemukan' }}
                </div>
            @endif
            <div class="filter-item">
                <strong>Diekspor pada:</strong> {{ $exported_at ?? now()->format('d/m/Y H:i') }}
            </div>
        </div>
    @endif



    <div class="table-container">
        @if ($overtimes && $overtimes->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Nama Karyawan</th>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 8%;">Mulai</th>
                        <th style="width: 8%;">Selesai</th>
                        <th style="width: 25%;">Alasan</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overtimes as $index => $overtime)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $overtime->user->name }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($overtime->date)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $overtime->start_time }}</td>
                            <td class="text-center">{{ $overtime->end_time }}</td>
                            <td class="reason-text">{{ $overtime->reason }}</td>
                            <td class="text-center">
                                <span class="status-badge status-{{ $overtime->status }}">
                                    @switch($overtime->status)
                                        @case('approved')
                                            Disetujui
                                        @break

                                        @case('rejected')
                                            Ditolak
                                        @break

                                        @default
                                            Pending
                                    @endswitch
                                </span>
                            </td>
                            <td class="notes-text">{{ $overtime->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>Tidak ada data overtime yang ditemukan untuk periode dan filter yang dipilih.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis pada {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ config('app.name', 'Sistem Absensi') }} - {{ config('app.url') }}</p>
    </div>
</body>

</html>
