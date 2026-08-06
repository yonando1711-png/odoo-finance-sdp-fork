@extends('layouts.app')

@section('title', $invoice->name)
@section('subtitle', 'Invoice DP Detail')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Navigation --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('invoice-dp.index') }}" class="flex items-center gap-2 text-sm text-slate-500 hover:text-emerald-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to list
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoice-dp.print', $invoice) }}" target="_blank" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print PDF
            </a>
            <a href="{{ route('invoice-dp.print-html', $invoice) }}" target="_blank" class="px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors flex items-center gap-1.5">
                Preview HTML
            </a>
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
@endsection
