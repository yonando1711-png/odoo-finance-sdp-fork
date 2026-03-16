<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (Setting::get('show_dashboard', '1') !== '1') {
            return redirect()->route('import');
        }

        $stats = [
            'total_entries' => JournalEntry::count(),
            'total_debit' => JournalLine::sum('debit'),
            'total_credit' => JournalLine::sum('credit'),
        ];

        $recentEntries = JournalEntry::with('lines')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Last 6 months cash flow (SQLite syntax)
        $monthlyFlow = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->select(
                DB::raw("strftime('%Y-%m', date) as month"),
                DB::raw("sum(debit) as total_debit"),
                DB::raw("sum(credit) as total_credit")
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('dashboard', compact('stats', 'recentEntries', 'monthlyFlow'));
    }
}
