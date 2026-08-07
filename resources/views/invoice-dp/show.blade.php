@extends('layouts.app')

@section('title', $invoice->name)
@section('subtitle', 'Invoice DP Detail')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="max-w-5xl mx-auto">
    {{-- Navigation --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('invoice-dp.index') }}" class="flex items-center gap-2 text-sm text-slate-500 hover:text-emerald-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to list
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="printInvoiceToHub('{{ $invoice->name }}', 'invoice_dp')" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print to Hub
            </button>
            <button type="button" onclick="printInvoice('{{ $invoice->name }}', '{{ route('invoice-dp.print', $invoice) }}')" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Print PDF
            </button>
        </div>
    </div>

    {{-- Invoice Header Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 mb-6">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ $invoice->name }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $invoice->journal_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold">Rp&nbsp;{{ number_format($invoice->amount_total, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Amount</p>
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Left Column --}}
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Customer (Telah Terima Dari)</p>
                    <p class="text-sm font-semibold mt-1">{{ $invoice->partner_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">NPWP</p>
                    <p class="text-sm mt-1 font-mono text-slate-700 dark:text-slate-300">{{ $invoice->partner_npwp ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Untuk Pembayaran</p>
                    <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $invoice->payment_description }}</p>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice Date (Tanggal)</p>
                    <p class="text-sm font-medium mt-1">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Reserved Lot (Nopol)</p>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $invoice->reserved_lot ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice PIC</p>
                    <p class="text-sm font-medium mt-1">{{ $invoice->invoice_pic ?: '-' }}</p>
                </div>
            </div>
        </div>
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
                
                window.showInvoicePreviewModal(htmlUrl, pdfUrl, refreshUrl);
            }
        });
    }
</script>
@include('partials.invoice-print-hub')
@endsection
