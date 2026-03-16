@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Overview of Cash & Bank activities')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total Entries</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['total_entries']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg text-cyan-600 dark:text-cyan-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total Debit (Inflow)</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['total_debit'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-violet-100 dark:bg-violet-900/30 rounded-lg text-violet-600 dark:text-violet-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total Credit (Outflow)</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['total_credit'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Monthly Flow --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="font-semibold">Monthly Cash Flow</h3>
                <span class="text-xs text-slate-500">Last 6 Months</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Month</th>
                            <th class="px-6 py-3 text-right font-medium">Debit</th>
                            <th class="px-6 py-3 text-right font-medium">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($monthlyFlow as $flow)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ \Carbon\Carbon::parse($flow->month)->format('F Y') }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($flow->total_debit, 2) }}</td>
                            <td class="px-6 py-4 text-right text-red-600 dark:text-red-400 font-mono">{{ number_format($flow->total_credit, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Entries --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="font-semibold">Recent Entries</h3>
                <a href="{{ route('journals.index') }}" class="text-xs text-emerald-500 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach($recentEntries as $entry)
                <div class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <div class="flex justify-between items-start mb-1">
                        <a href="{{ route('journals.show', $entry) }}" class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                            {{ $entry->move_name }}
                        </a>
                        <span class="text-xs text-slate-500">{{ $entry->date->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-600 dark:text-slate-400 truncate max-w-[200px]">{{ $entry->partner_name ?? 'No Partner' }}</span>
                        <span class="font-mono text-xs font-semibold {{ $entry->amount_total_signed >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($entry->amount_total_signed, 2) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
