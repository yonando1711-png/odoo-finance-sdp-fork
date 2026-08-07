<?php

namespace App\Http\Controllers;

use App\Models\InvoiceDp;
use App\Models\InvoiceDpLine;
use App\Models\ImportLog;
use App\Models\Setting;
use App\Models\PrintLog;
use App\Services\OdooService;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceDpController extends Controller
{
    /**
     * Display the invoice DP listing page
     */
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'invoice_date');
        $dir = $request->input('dir', 'desc');

        $query = InvoiceDp::with('lines');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%")
                  ->orWhere('ref', 'like', "%{$search}%")
                  ->orWhere('reserved_lot', 'like', "%{$search}%")
                  ->orWhere('partner_npwp', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        // Sorting
        $allowedSorts = ['name', 'invoice_date', 'partner_name', 'ref', 'reserved_lot', 'amount_untaxed', 'amount_tax', 'amount_total'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'invoice_date';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $query->orderBy($sort, $dir);
        if ($sort !== 'name') {
            $query->orderBy('name', 'desc');
        }

        $perPage = $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100])) $perPage = 25;

        $invoices = $query->paginate($perPage)->withQueryString();

        // Summary stats
        $statsQuery = InvoiceDp::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('partner_name', 'like', "%{$search}%")
                        ->orWhere('ref', 'like', "%{$search}%")
                        ->orWhere('reserved_lot', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->where('invoice_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->where('invoice_date', '<=', $request->date_to);
            });

        $stats = $statsQuery->selectRaw("
                count(*) as total_invoices,
                sum(amount_untaxed) as total_untaxed,
                sum(amount_tax) as total_tax,
                sum(amount_total) as total_amount
            ")
            ->first()
            ->toArray();

        return view('invoice-dp.index', compact('invoices', 'stats', 'sort', 'dir', 'perPage'));
    }

    /**
     * Get all Odoo IDs for the given date range to sync
     */
    public function getSyncIds(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        try {
            $odoo = new OdooService();
            $result = $odoo->getInvoiceDpIds(
                $request->input('date_from'),
                $request->input('date_to')
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Odoo fetch failed: ' . ($result['message'] ?? 'Unknown error')
                ]);
            }

            // Cleanup cancelled invoices
            try {
                $syncService = new SyncService();
                $syncService->cleanupCancelledInvoices($odoo, $request->input('date_from'), $request->input('date_to'));
            } catch (\Exception $e) {
                Log::error("Failed to clean up cancelled invoices in getSyncIds: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'ids' => $result['ids'],
                'count' => $result['count']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch IDs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync a specific batch of IDs
     */
    public function syncBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        try {
            $odoo = new OdooService();
            $result = $odoo->fetchInvoiceDpsByIds($request->input('ids'));

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Odoo batch fetch failed: ' . ($result['message'] ?? 'Unknown error')
                ]);
            }

            if (empty($result['data'])) {
                return response()->json([
                    'success' => true,
                    'count' => 0,
                    'message' => 'No data returned for this batch.'
                ]);
            }

            $syncService = new SyncService();
            $savedCount = $syncService->saveInvoiceDps($result['data']);

            return response()->json([
                'success' => true,
                'count' => $savedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch sync failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display detailed view of an Invoice DP
     */
    public function show($id)
    {
        $invoice = InvoiceDp::with('lines')->findOrFail($id);
        return view('invoice-dp.show', compact('invoice'));
    }

    /**
     * Render PDF for printing / downloading
     */
    public function printPdf($id)
    {
        $invoice = InvoiceDp::with('lines')->findOrFail($id);

        $displayCount = (int)$invoice->print_count + 1;
        $printNumDisplay = str_pad($displayCount, 2, '0', STR_PAD_LEFT);

        $invoice->increment('print_count');
        $invoice->update(['last_printed_at' => now()]);

        $enableWatermark = Setting::get('enable_pdf_watermark', '1');

        $pdf = Pdf::loadView('invoice-dp.pdf', [
            'invoices' => collect([$invoice]),
            'enableWatermark' => $enableWatermark,
            'printNumDisplay' => $printNumDisplay,
            'isPdf' => true,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Invoice_DP_' . str_replace('/', '_', $invoice->name) . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Render HTML preview for live preview popup modal
     */
    public function printHtml($id)
    {
        $invoice = InvoiceDp::with('lines')->findOrFail($id);

        $displayCount = (int)$invoice->print_count + 1;
        $printNumDisplay = str_pad($displayCount, 2, '0', STR_PAD_LEFT);

        $enableWatermark = Setting::get('enable_pdf_watermark', '1');

        return view('invoice-dp.pdf', [
            'invoices' => collect([$invoice]),
            'enableWatermark' => $enableWatermark,
            'printNumDisplay' => $printNumDisplay,
            'isHtml' => true,
        ]);
    }

    /**
     * Refresh single invoice from Odoo
     */
    public function refreshFromOdoo($id)
    {
        $invoice = InvoiceDp::findOrFail($id);
        if (!$invoice->odoo_id) {
            return response()->json(['success' => false, 'message' => 'No Odoo ID associated with this record.']);
        }

        try {
            $odoo = new OdooService();
            $result = $odoo->fetchInvoiceDpsByIds([$invoice->odoo_id]);

            if ($result['success'] && !empty($result['data'])) {
                $syncService = new SyncService();
                $syncService->saveInvoiceDps($result['data']);
                return response()->json(['success' => true, 'message' => 'Refreshed successfully from Odoo.']);
            }

            return response()->json(['success' => false, 'message' => 'Record not found in Odoo or error occurred.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Refresh error: ' . $e->getMessage()]);
        }
    }
}
