<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            {{ $this->form }}

            <div style="margin-top: 16px; display: flex; gap: 12px;">
                <button wire:click="applyFilter" wire:loading.attr="disabled"
                    style="background: #3b82f6; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                    <span wire:loading.remove wire:target="applyFilter">🔍 Terapkan Filter</span>
                    <span wire:loading wire:target="applyFilter">Loading...</span>
                </button>

                <button wire:click="resetFilter" wire:loading.attr="disabled"
                    style="background: #6b7280; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
                    <span wire:loading.remove wire:target="resetFilter">🔄 Reset Filter</span>
                    <span wire:loading wire:target="resetFilter">Loading...</span>
                </button>
            </div>

            <div
                style="margin-top: 12px; padding: 8px 12px; background: #f3f4f6; border-radius: 4px; font-size: 14px; color: #374151;">
                {{ $this->getFilterInfo() }} | Total Records: {{ $this->getTableRecordCount() }}
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div
                style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 24px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; margin-bottom: 8px;">{{ $this->getTotalOvertime() }}
                </div>
                <div style="font-size: 16px; opacity: 0.9;">Total Lembur</div>
            </div>

            <div
                style="background: linear-gradient(135deg, #10b981, #047857); color: white; padding: 24px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; margin-bottom: 8px;">{{ $this->getApprovedOvertime() }}
                </div>
                <div style="font-size: 16px; opacity: 0.9;">Disetujui</div>
            </div>

            <div
                style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 24px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; margin-bottom: 8px;">{{ $this->getPendingOvertime() }}
                </div>
                <div style="font-size: 16px; opacity: 0.9;">Menunggu</div>
            </div>

            <div
                style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 24px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; margin-bottom: 8px;">{{ $this->getRejectedOvertime() }}
                </div>
                <div style="font-size: 16px; opacity: 0.9;">Ditolak</div>
            </div>
        </div>

        {{-- Table --}}
        <div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
