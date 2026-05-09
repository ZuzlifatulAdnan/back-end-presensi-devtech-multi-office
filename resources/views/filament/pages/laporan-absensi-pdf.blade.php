<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
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

        .status-hadir {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-belum-pulang {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-tidak-masuk {
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
        <h1>LAPORAN ABSENSI</h1>
        <p>{{ config('app.name', 'Sistem Absensi') }}</p>
        <p>Diekspor pada: {{ $exported_at ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filter-info">
        <h3>Informasi Laporan:</h3>
        <div class="filter-item">
            <strong>Total Records:</strong> {{ $total_records ?? count($attendances) }}
        </div>
        <div class="filter-item">
            <strong>Diekspor pada:</strong> {{ $exported_at ?? now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="table-container">
        @if ($attendances && $attendances->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Nama Karyawan</th>
                        <th style="width: 15%;">Jabatan</th>
                        <th style="width: 15%;">Departemen</th>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 8%;">Jam Masuk</th>
                        <th style="width: 8%;">Jam Keluar</th>
                        <th style="width: 10%;">Jam Kerja</th>
                        <th style="width: 7%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $index => $attendance)
                        @php
                            $timeIn = $attendance->time_in
                                ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i')
                                : '-';
                            $timeOut = $attendance->time_out
                                ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i')
                                : '-';

                            $workingHours = '-';
                            if ($attendance->time_in && $attendance->time_out) {
                                $start = \Carbon\Carbon::parse($attendance->time_in);
                                $end = \Carbon\Carbon::parse($attendance->time_out);
                                $diff = $start->diff($end);
                                $workingHours = sprintf('%d jam %d menit', $diff->h, $diff->i);
                            }

                            $status = 'Hadir';
                            $statusClass = 'status-hadir';
                            if (!$attendance->time_in) {
                                $status = 'Tidak Masuk';
                                $statusClass = 'status-tidak-masuk';
                            } elseif (!$attendance->time_out) {
                                $status = 'Belum Pulang';
                                $statusClass = 'status-belum-pulang';
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->user->position ?? '-' }}</td>
                            <td>{{ $attendance->user->department ?? '-' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                            </td>
                            <td class="text-center">{{ $timeIn }}</td>
                            <td class="text-center">{{ $timeOut }}</td>
                            <td class="text-center">{{ $workingHours }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>Tidak ada data absensi yang ditemukan untuk periode dan filter yang dipilih.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis pada {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ config('app.name', 'Sistem Absensi') }} - {{ config('app.url') }}</p>
    </div>
</body>

</html>
