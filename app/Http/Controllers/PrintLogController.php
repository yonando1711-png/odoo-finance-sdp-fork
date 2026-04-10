<?php

namespace App\Http\Controllers;

use App\Models\PrintLog;
use Illuminate\Http\Request;

class PrintLogController extends Controller
{
    /**
     * Update the Kuitansi override text mapped to a specific invoice.
     */
    public function updateKuitansi(Request $request)
    {
        $request->validate([
            'invoice_name' => 'required|string',
            'pembayaran_1' => 'nullable|string|max:110',
            'pembayaran_2' => 'nullable|string|max:110',
            'pembayaran_3' => 'nullable|string|max:110',
            'pembayaran_4' => 'nullable|string|max:110',
        ]);

        $lines = [];
        for ($i = 1; $i <= 4; $i++) {
            $val = trim($request->input("pembayaran_{$i}"));
            if (!empty($val)) {
                $lines[] = $val;
            }
        }

        $pembayaranText = empty($lines) ? null : implode("\n", $lines);

        $printLog = PrintLog::firstOrCreate(
            ['invoice_name' => $request->invoice_name]
        );

        $printLog->kuitansi_pembayaran = $pembayaranText;
        $printLog->save();

        return response()->json([
            'success' => true,
            'message' => 'Kuitansi override text saved successfully.'
        ]);
    }
}
