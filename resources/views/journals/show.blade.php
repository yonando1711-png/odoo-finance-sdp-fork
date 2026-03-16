@extends('layouts.app')

@section('title', $entry->move_name)
@section('subtitle', 'Journal entry detail')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Header Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <a href="{{ route('journals.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-emerald-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to list
        </a>

        <div class="flex items-center gap-2">
            {{-- Navigation --}}
            <div class="flex items-center bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-1 shadow-sm">
                @if($prev)
                <a href="{{ route('journals.show', $prev) }}" title="Previous (Newer)" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @else
                <span class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @endif

                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-x border-slate-100 dark:border-slate-700 mx-1">
                    Nav
                </div>

                @if($next)
                <a href="{{ route('journals.show', $next) }}" title="Next (Older)" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @else
                <span class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                @endif
            </div>

            <a href="{{ route('journals.print', $entry) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print PDF
            </a>
        </div>
    </div>

    {{-- Header Detail Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm overflow-hidden relative">
        {{-- Balance Status Badge --}}
        @php
            $debitTotal = $entry->lines->sum('debit');
            $creditTotal = $entry->lines->sum('credit');
            $isBalanced = abs($debitTotal - $creditTotal) < 0.01;
        @endphp
        
        <div class="absolute top-0 right-0">
            @if($isBalanced)
            <div class="bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                BALANCED
            </div>
            @else
            <div class="bg-red-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                IMBALANCED
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-slate-500 mb-1">Move Name</p>
                <p class="font-mono font-bold text-emerald-600 dark:text-emerald-400 select-all">{{ $entry->move_name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Date</p>
                <p class="font-semibold">{{ $entry->date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Journal</p>
                <p class="font-medium text-slate-700 dark:text-slate-300">{{ $entry->journal_name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-1">Partner</p>
                <p class="font-medium truncate" title="{{ $entry->partner_name }}">{{ $entry->partner_name ?? '-' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-slate-500 mb-1">Reference</p>
                <p class="font-medium text-slate-600 dark:text-slate-400 italic">"{{ $entry->ref ?? 'No reference' }}"</p>
            </div>
            <div class="col-span-2 text-right">
                <p class="text-xs text-slate-500 mb-1">Total Signed Amount</p>
                <p class="font-mono font-bold text-xl {{ $entry->amount_total_signed >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    Rp {{ number_format($entry->amount_total_signed, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Line Items Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
            <h3 class="font-semibold">Line Items</h3>
            <span class="text-xs font-medium px-2 py-0.5 bg-slate-200 dark:bg-slate-700 rounded-full">{{ $entry->lines->count() }} lines</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">#</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Account</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Description</th>
                        <th class="px-right py-3 text-right font-medium text-slate-600 dark:text-slate-400">Debit</th>
                        <th class="px-right py-3 text-right font-medium text-slate-600 dark:text-slate-400">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($entry->lines as $i => $line)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="font-mono text-violet-600 dark:text-violet-400 font-bold text-xs">{{ $line->account_code }}</span>
                                <span class="text-slate-500 text-[10px] leading-tight mt-0.5">{{ $line->account_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-slate-700 dark:text-slate-300 text-xs">{{ $line->display_name ?: '-' }}</span>
                                @if($line->ref)
                                <span class="text-slate-400 text-[10px] italic mt-0.5">Ref: {{ $line->ref }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-mono {{ $line->debit > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '0,00' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono {{ $line->credit > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
                            {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '0,00' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 font-bold">
                    <tr>
                        <td class="px-4 py-4 text-right" colspan="3">Totals</td>
                        <td class="px-4 py-4 text-right font-mono text-emerald-600 dark:text-emerald-400 underline decoration-double">
                            Rp {{ number_format($debitTotal, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-right font-mono text-red-600 dark:text-red-400 underline decoration-double">
                            Rp {{ number_format($creditTotal, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
