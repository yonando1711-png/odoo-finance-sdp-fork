@extends('layouts.app')

@section('title', 'Invoice DP')
@section('subtitle', 'Invoice Down Payment entries from Odoo (INVDP)')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div x-data="{
    syncOpen: false,
    syncing: false,
    syncMessage: '',
    syncSuccess: null,
    syncTotal: 0,
    syncCurrent: 0,
    syncProgress: 0,
    columns: {
        name: { visible: true, width: '170px', label: 'Invoice #' },
        date: { visible: true, width: '110px', label: 'Date' },
        partner: { visible: true, width: '190px', label: 'Customer' },
        npwp: { visible: true, width: '140px', label: 'NPWP' },
        lot: { visible: true, width: '130px', label: 'Reserved Lot (Nopol)' },
        payment_desc: { visible: true, width: '220px', label: 'Untuk Pembayaran' },
        untaxed: { visible: true, width: '120px', label: 'Untaxed' },
        tax: { visible: true, width: '100px', label: 'Tax' },
        total: { visible: true, width: '130px', label: 'Total' }
    },
    init() {
        const saved = localStorage.getItem('invdp_column_settings');
        if (saved) {
            const parsed = JSON.parse(saved);
            Object.keys(this.columns).forEach(key => {
                if (parsed[key]) {
                    this.columns[key].visible = parsed[key].visible;
                    this.columns[key].width = parsed[key].width;
                }
            });
        }
        this.$watch('columns', value => {
            localStorage.setItem('invdp_column_settings', JSON.stringify(value));
        }, { deep: true });
    },
    resize(key, event) {
        const startX = event.pageX;
        const startWidth = parseInt(this.columns[key].width);
        const mouseMoveHandler = (e) => {
            const diff = e.pageX - startX;
            const newWidth = Math.max(60, startWidth + diff);
            this.columns[key].width = newWidth + 'px';
        };
        const mouseUpHandler = () => {
            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);
            document.body.style.cursor = 'default';
            document.body.style.userSelect = 'auto';
        };
        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    },
    get visibleColumnCount() {
        return Object.values(this.columns).filter(c => c.visible).length + 2;
    },
    async doSync() {
        const dateFrom = document.getElementById('sync_date_from').value;
        const dateTo = document.getElementById('sync_date_to').value;
        if (!dateFrom || !dateTo) {
            this.syncMessage = 'Please select both dates.';
            this.syncSuccess = false;
            return;
        }
        
        this.syncing = true;
        this.syncMessage = 'Fetching IDs from Odoo...';
        this.syncSuccess = null;
        this.syncTotal = 0;
        this.syncCurrent = 0;
        this.syncProgress = 0;

        try {
            const idRes = await fetch('{{ route('invoice-dp.sync-ids', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ date_from: dateFrom, date_to: dateTo })
            });
            const idData = await idRes.json();
            
            if (!idData.success) {
                this.syncSuccess = false;
                this.syncMessage = idData.message || 'Failed to fetch IDs.';
                this.syncing = false;
                return;
            }

            const allIds = idData.ids || [];
            this.syncTotal = allIds.length;
            
            if (this.syncTotal === 0) {
                this.syncSuccess = true;
                this.syncMessage = 'No invoices found for the selected range.';
                this.syncing = false;
                return;
            }

            const chunkSize = 500;
            let processedCount = 0;

            for (let i = 0; i < allIds.length; i += chunkSize) {
                const batch = allIds.slice(i, i + chunkSize);
                this.syncMessage = `Syncing batch ${Math.floor(i/chunkSize) + 1} (${i + 1} - ${Math.min(i + chunkSize, allIds.length)} of ${allIds.length})...`;
                
                const batchRes = await fetch('{{ route('invoice-dp.sync-batch', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: batch })
                });
                
                const batchData = await batchRes.json();
                if (!batchData.success) {
                    throw new Error(batchData.message || 'Batch sync failed');
                }

                processedCount += (batchData.count || 0);
                this.syncCurrent = Math.min(i + chunkSize, allIds.length);
                this.syncProgress = Math.round((this.syncCurrent / this.syncTotal) * 100);
            }

            this.syncSuccess = true;
            this.syncMessage = `Successfully synced ${processedCount} DP invoices!`;
            setTimeout(() => window.location.reload(), 1500);

        } catch (e) {
            this.syncSuccess = false;
            this.syncMessage = 'Sync error: ' + e.message;
        } finally {
            this.syncing = false;
        }
    }
}">

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['total_invoices'] ?? 0) }}</p>
            <p class="text-xs text-slate-500">Total Invoices</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-2xl font-bold">Rp {{ number_format($stats['total_untaxed'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Untaxed</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($stats['total_tax'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Tax</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Amount</p>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div x-data="{ filtersOpen: true }" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between cursor-pointer group" @click="filtersOpen = !filtersOpen">
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-1 text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span class="font-medium">Filters & Actions</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Column Settings --}}
                <div x-data="{ open: false }" class="relative" @click.stop="">
                    <button @click="open = !open" class="flex items-center gap-1.5 px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Columns</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-[60] p-2">
                        <template x-for="(col, key) in columns" :key="key">
                            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="col.visible" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5 dark:bg-slate-900 dark:border-slate-600">
                                <span class="text-xs text-slate-700 dark:text-slate-300" x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="filtersOpen" x-cloak class="p-4 border-t border-slate-200 dark:border-slate-700">
            <form method="GET" action="{{ route('invoice-dp.index') }}">
                <div class="flex flex-wrap items-end gap-3 mb-3">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, Customer, Reserved Lot..."
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div class="flex gap-2 items-end pb-[2px]">
                        <button type="submit" class="px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Filter</button>
                        <a href="{{ route('invoice-dp.index') }}" class="px-3 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Clear</a>
                        <button type="button" @click="syncOpen = !syncOpen" class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Sync Odoo
                        </button>
                    </div>
                </div>
            </form>

            {{-- Sync Panel --}}
            <div x-show="syncOpen" x-cloak class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                <h3 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-3">Sync Invoice DP from Odoo</h3>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date From</label>
                        <input type="date" id="sync_date_from" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date To</label>
                        <input type="date" id="sync_date_to" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <button @click="doSync()" :disabled="syncing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg x-show="syncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="syncing ? 'Syncing...' : 'Start Sync'"></span>
                    </button>
                </div>
                
                {{-- Progress Bar --}}
                <div x-show="syncing && syncTotal > 0" x-cloak class="mt-4">
                    <div class="flex justify-between text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1 uppercase tracking-widest">
                        <span x-text="'Processing ' + syncCurrent + ' of ' + syncTotal"></span>
                        <span x-text="syncProgress + '%'"></span>
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-900 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="'width: ' + syncProgress + '%'"></div>
                    </div>
                </div>

                <div x-show="syncMessage" x-cloak class="mt-3 text-xs" :class="syncSuccess === true ? 'text-emerald-600 font-medium' : (syncSuccess === false ? 'text-rose-600 font-medium' : 'text-blue-600')">
                    <span x-text="syncMessage"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Data Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 text-slate-500 uppercase tracking-wider font-semibold">
                        <th x-show="columns.name.visible" :style="{ width: columns.name.width }" class="p-3 relative group">
                            Invoice #
                            <div @mousedown="resize('name', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.date.visible" :style="{ width: columns.date.width }" class="p-3 relative group">
                            Date
                            <div @mousedown="resize('date', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.partner.visible" :style="{ width: columns.partner.width }" class="p-3 relative group">
                            Customer
                            <div @mousedown="resize('partner', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.npwp.visible" :style="{ width: columns.npwp.width }" class="p-3 relative group">
                            NPWP
                            <div @mousedown="resize('npwp', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.lot.visible" :style="{ width: columns.lot.width }" class="p-3 relative group">
                            Reserved Lot (Nopol)
                            <div @mousedown="resize('lot', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.payment_desc.visible" :style="{ width: columns.payment_desc.width }" class="p-3 relative group">
                            Untuk Pembayaran
                            <div @mousedown="resize('payment_desc', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.untaxed.visible" :style="{ width: columns.untaxed.width }" class="p-3 text-right relative group">
                            Untaxed
                            <div @mousedown="resize('untaxed', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.tax.visible" :style="{ width: columns.tax.width }" class="p-3 text-right relative group">
                            Tax
                            <div @mousedown="resize('tax', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th x-show="columns.total.visible" :style="{ width: columns.total.width }" class="p-3 text-right relative group">
                            Total
                            <div @mousedown="resize('total', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize opacity-0 group-hover:opacity-100 bg-emerald-500"></div>
                        </th>
                        <th class="p-3 text-center w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                        <td x-show="columns.name.visible" class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            <a href="{{ route('invoice-dp.show', $inv) }}" class="hover:underline">{{ $inv->name }}</a>
                        </td>
                        <td x-show="columns.date.visible" class="p-3 text-slate-500">
                            {{ $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '-' }}
                        </td>
                        <td x-show="columns.partner.visible" class="p-3 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $inv->partner_name }}
                        </td>
                        <td x-show="columns.npwp.visible" class="p-3 font-mono text-slate-500">
                            {{ $inv->partner_npwp ?: '-' }}
                        </td>
                        <td x-show="columns.lot.visible" class="p-3">
                            @if($inv->reserved_lot)
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-mono text-[11px] font-bold">
                                    {{ $inv->reserved_lot }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td x-show="columns.payment_desc.visible" class="p-3 font-medium text-slate-700 dark:text-slate-300">
                            {{ $inv->payment_description }}
                        </td>
                        <td x-show="columns.untaxed.visible" class="p-3 text-right">
                            {{ number_format($inv->amount_untaxed, 0, ',', '.') }}
                        </td>
                        <td x-show="columns.tax.visible" class="p-3 text-right text-slate-500">
                            {{ number_format($inv->amount_tax, 0, ',', '.') }}
                        </td>
                        <td x-show="columns.total.visible" class="p-3 text-right font-bold text-slate-900 dark:text-slate-100">
                            {{ number_format($inv->amount_total, 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('invoice-dp.show', $inv) }}" title="View Detail" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" onclick="openPrintModal('{{ $inv->id }}', '{{ $inv->name }}')" title="Pilih Jenis Cetakan" class="p-1 rounded hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td :colspan="visibleColumnCount" class="p-8 text-center text-slate-500">
                            No Invoice DP records found. Try running a sync from Odoo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

{{-- 1. "Pilih Jenis Cetakan" Modal --}}
<div id="printModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-center text-slate-800 dark:text-slate-100 mb-6">Pilih Jenis Cetakan</h3>
        
        <div class="space-y-4 mb-6">
            <label class="p-4 border-2 border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-xl cursor-pointer flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100">Tanda Terima Uang Muka</h4>
                        <p class="text-xs text-slate-500">Official DP Receipt format (SDP/FR/BC/02, Rev.01).</p>
                    </div>
                </div>
                <input type="radio" name="print_choice" value="dp_receipt" checked class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            <button type="button" onclick="closePrintModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium text-sm rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Batal</button>
            <button type="button" onclick="triggerPreviewModal()" class="px-5 py-2 bg-emerald-600 text-white font-medium text-sm rounded-lg hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20">Preview</button>
        </div>
    </div>
</div>

{{-- 2. Interactive Live HTML Preview Popup Modal --}}
<div id="previewModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4">
    <div class="bg-slate-900 rounded-2xl w-full max-w-5xl h-[92vh] flex flex-col shadow-2xl border border-slate-800 overflow-hidden">
        {{-- Preview Modal Header --}}
        <div class="px-6 py-4 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                <span class="text-xs font-semibold text-slate-400">Preview Tanda Terima Uang Muka</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="previewRefreshBtn" class="px-3.5 py-1.5 bg-amber-500 text-slate-950 text-xs font-bold rounded-lg hover:bg-amber-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh from Odoo</span>
                </button>
                <a id="previewPdfDownloadBtn" href="#" target="_blank" class="px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-500 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF (Clean)</span>
                </a>
                <button type="button" onclick="closePreviewModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Preview iFrame Content --}}
        <div class="flex-1 bg-white p-2">
            <iframe id="previewIframe" class="w-full h-full border-0 rounded-lg"></iframe>
        </div>
    </div>
</div>

<script>
    let activeInvoiceId = null;
    let activeInvoiceName = null;

    function openPrintModal(id, name) {
        activeInvoiceId = id;
        activeInvoiceName = name;
        document.getElementById('printModal').classList.remove('hidden');
    }

    function closePrintModal() {
        document.getElementById('printModal').classList.add('hidden');
    }

    function triggerPreviewModal() {
        closePrintModal();
        if (!activeInvoiceId) return;

        const previewUrl = `/invoice-dp/${activeInvoiceId}/html`;
        const pdfUrl = `/invoice-dp/${activeInvoiceId}/pdf`;

        document.getElementById('previewIframe').src = previewUrl;
        document.getElementById('previewPdfDownloadBtn').href = pdfUrl;
        
        document.getElementById('previewRefreshBtn').onclick = async function() {
            Swal.fire({
                title: 'Refreshing from Odoo...',
                text: 'Fetching latest data for ' + activeInvoiceName,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch(`/invoice-dp/${activeInvoiceId}/refresh`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Refreshed!', data.message, 'success');
                    document.getElementById('previewIframe').src = previewUrl;
                } else {
                    Swal.fire('Error', data.message || 'Refresh failed', 'error');
                }
            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            }
        };

        document.getElementById('previewModal').classList.remove('hidden');
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
        document.getElementById('previewIframe').src = '';
    }
</script>
@endsection
