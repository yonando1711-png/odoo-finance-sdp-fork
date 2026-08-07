@extends('layouts.app')

@section('title', 'Invoice DP')
@section('subtitle', 'Invoice Down Payment entries from Odoo')

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
        name: { visible: true, width: '180px', label: 'Invoice #' },
        date: { visible: true, width: '110px', label: 'Date' },
        partner: { visible: true, width: '200px', label: 'Customer' },
        npwp: { visible: true, width: '140px', label: 'NPWP' },
        lot: { visible: true, width: '150px', label: 'Reserved Lot (Nopol)' },
        payment_desc: { visible: true, width: '220px', label: 'Untuk Pembayaran' },
        untaxed: { visible: true, width: '130px', label: 'Untaxed' },
        tax: { visible: true, width: '110px', label: 'Tax' },
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
        return Object.values(this.columns).filter(c => c.visible).length + 1;
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

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-2xl font-bold text-emerald-500">{{ number_format($stats['total_invoices'] ?? 0) }}</p>
            <p class="text-xs text-slate-500">Total Invoices</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-2xl font-bold text-cyan-500">Rp {{ number_format($stats['total_untaxed'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Untaxed</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-2xl font-bold text-amber-500">Rp {{ number_format($stats['total_tax'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Tax</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-2xl font-bold text-violet-500">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Total Amount</p>
        </div>
    </div>

    {{-- Filters --}}
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
                <button class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-5 h-5 transition-transform duration-300" :class="filtersOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>

        <div x-show="filtersOpen" x-cloak x-transition class="p-4 border-t border-slate-200 dark:border-slate-700">
            <form method="GET" action="{{ route('invoice-dp.index', [], false) }}">
            <div class="flex flex-wrap items-end gap-3 mb-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, customer, reference..."
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
                        Sync
                    </button>
                </div>
            </div>
            </form>

            {{-- Sync Panel --}}
            <div x-show="syncOpen" x-cloak x-transition class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
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
                    <div class="w-full bg-blue-100 dark:bg-blue-900/50 rounded-full h-2 overflow-hidden border border-blue-200 dark:border-blue-800">
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-300 relative"
                            :style="'width:' + syncProgress + '%'">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <div x-show="syncMessage" x-cloak class="mt-3 text-sm px-3 py-2 rounded-lg" :class="syncSuccess === true ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : (syncSuccess === false ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300')" x-text="syncMessage"></div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[75vh]">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 select-none">
                    <tr>
                        <th class="px-3 py-3 w-10 text-center border-b border-slate-200 dark:border-slate-700 sticky top-0 left-0 bg-slate-50 dark:bg-slate-900 z-50">
                            <input type="checkbox" id="selectAllCheckbox" title="Select All" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer dark:bg-slate-800 dark:border-slate-600">
                        </th>

                        {{-- Invoice # --}}
                        <th x-show="columns.name.visible" :style="{ width: columns.name.width, minWidth: columns.name.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('sort') === 'name' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-emerald-600 transition-colors">
                                Invoice #
                                @if(request('sort') === 'name')
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('dir') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </a>
                            <div @mousedown="resize('name', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Date --}}
                        <th x-show="columns.date.visible" :style="{ width: columns.date.width, minWidth: columns.date.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_date', 'dir' => request('sort', 'invoice_date') === 'invoice_date' && request('dir', 'desc') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-emerald-600 transition-colors">
                                Date
                                @if(request('sort', 'invoice_date') === 'invoice_date')
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('dir', 'desc') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </a>
                            <div @mousedown="resize('date', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Customer --}}
                        <th x-show="columns.partner.visible" :style="{ width: columns.partner.width, minWidth: columns.partner.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'partner_name', 'dir' => request('sort') === 'partner_name' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-emerald-600 transition-colors">
                                Customer
                                @if(request('sort') === 'partner_name')
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('dir') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </a>
                            <div @mousedown="resize('partner', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- NPWP --}}
                        <th x-show="columns.npwp.visible" :style="{ width: columns.npwp.width, minWidth: columns.npwp.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            NPWP
                            <div @mousedown="resize('npwp', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Reserved Lot --}}
                        <th x-show="columns.lot.visible" :style="{ width: columns.lot.width, minWidth: columns.lot.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            Reserved Lot (Nopol)
                            <div @mousedown="resize('lot', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Untuk Pembayaran --}}
                        <th x-show="columns.payment_desc.visible" :style="{ width: columns.payment_desc.width, minWidth: columns.payment_desc.width }" class="group relative px-3 py-3 text-left font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            Untuk Pembayaran
                            <div @mousedown="resize('payment_desc', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Untaxed --}}
                        <th x-show="columns.untaxed.visible" :style="{ width: columns.untaxed.width, minWidth: columns.untaxed.width }" class="group relative px-3 py-3 text-right font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            Untaxed
                            <div @mousedown="resize('untaxed', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Tax --}}
                        <th x-show="columns.tax.visible" :style="{ width: columns.tax.width, minWidth: columns.tax.width }" class="group relative px-3 py-3 text-right font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            Tax
                            <div @mousedown="resize('tax', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>

                        {{-- Total --}}
                        <th x-show="columns.total.visible" :style="{ width: columns.total.width, minWidth: columns.total.width }" class="group relative px-3 py-3 text-right font-medium text-slate-600 dark:text-slate-400 sticky top-0 bg-slate-50 dark:bg-slate-900 z-40">
                            Total
                            <div @mousedown="resize('total', $event)" class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize group-hover:bg-emerald-500/30 transition-colors"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox" name="selected_ids[]" value="{{ $invoice->id }}" class="entry-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer dark:bg-slate-800 dark:border-slate-600">
                        </td>
                        <td x-show="columns.name.visible" class="px-3 py-2 font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('invoice-dp.show', $invoice) }}" class="hover:underline">{{ $invoice->name }}</a>
                                <button type="button" onclick="printInvoiceToHub('{{ $invoice->name }}', 'invoice_dp')" title="Print to Hub" class="text-slate-400 hover:text-emerald-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </button>
                                <button type="button" onclick="printInvoice('{{ $invoice->name }}', '{{ route('invoice-dp.print', $invoice) }}')" title="Print PDF" class="text-slate-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </button>
                            </div>
                        </td>
                        <td x-show="columns.date.visible" class="px-3 py-2 text-xs text-slate-500 whitespace-nowrap">{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '-' }}</td>
                        <td x-show="columns.partner.visible" class="px-3 py-2 text-xs">{{ $invoice->partner_name }}</td>
                        <td x-show="columns.npwp.visible" class="px-3 py-2 text-xs font-mono text-slate-500">{{ $invoice->partner_npwp ?: '-' }}</td>
                        <td x-show="columns.lot.visible" class="px-3 py-2 text-xs font-mono font-bold text-amber-600 dark:text-amber-400">
                            {{ $invoice->reserved_lot ?: '-' }}
                        </td>
                        <td x-show="columns.payment_desc.visible" class="px-3 py-2 text-xs text-slate-700 dark:text-slate-300 font-medium">{{ $invoice->payment_description }}</td>
                        <td x-show="columns.untaxed.visible" class="px-3 py-2 text-right font-mono text-xs whitespace-nowrap">{{ number_format($invoice->amount_untaxed, 0, ',', '.') }}</td>
                        <td x-show="columns.tax.visible" class="px-3 py-2 text-right font-mono text-xs text-amber-600 dark:text-amber-400 whitespace-nowrap">{{ number_format($invoice->amount_tax, 0, ',', '.') }}</td>
                        <td x-show="columns.total.visible" class="px-3 py-2 text-right font-mono text-xs font-semibold whitespace-nowrap">{{ number_format($invoice->amount_total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td :colspan="visibleColumnCount" class="px-4 py-12 text-center">
                            <div class="text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                No Invoice DP records found. Try running a sync from Odoo.
                            </div>
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

<script>
    function printInvoice(name, url) {
        Swal.fire({
            title: 'Pilih Jenis Cetakan',
            html: `
                <div class="text-left py-2">
                    <div class="space-y-3" id="dp-print-options-group">
                        <label class="option-card p-4 border-2 rounded-xl cursor-pointer flex items-center gap-4 bg-emerald-50 border-emerald-500 shadow-sm transition-all active-option" data-value="dp_receipt">
                            <input type="radio" name="print_type" value="dp_receipt" class="hidden" checked>
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800 text-sm">Tanda Terima Uang Muka</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Official DP Receipt format (SDP/FR/BC/02, Rev.01).</p>
                            </div>
                            <div class="radio-indicator w-5 h-5 rounded-full border-2 border-emerald-500 flex items-center justify-center">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                            </div>
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Preview',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            confirmButtonColor: '#10b981',
            width: '450px'
        }).then((result) => {
            if (result.isConfirmed) {
                const htmlUrl = url.replace('/pdf', '/html');
                const pdfUrl = url;
                const refreshUrl = url.replace('/pdf', '/refresh');
                
                // Show shared preview modal popup with Refresh & Download PDF buttons
                window.showInvoicePreviewModal(htmlUrl, pdfUrl, refreshUrl);
            }
        });
    }
</script>
@include('partials.invoice-print-hub')
@endsection
