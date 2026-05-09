<div class="flex flex-col gap-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nama Karyawan
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->user->name }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tanggal
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">
                {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Jabatan
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->user->position ?? '-' }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Departemen
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->user->department ?? '-' }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Jam Masuk
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $timeIn }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Jam Keluar
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $timeOut }}</p>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Total Jam Kerja
            </label>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $workingHours }}</p>
        </div>

        @if ($record->latlon_in)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Lokasi Check-in
                </label>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->latlon_in }}</p>
            </div>
        @endif

        @if ($record->latlon_out)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Lokasi Check-out
                </label>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->latlon_out }}</p>
            </div>
        @endif
    </div>
</div>
