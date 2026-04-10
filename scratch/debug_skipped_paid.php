<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OdooService;

$odoo = app(OdooService::class);
$from = '2025-04-01';
$to = '2026-04-24';

$domain = [
    ['invoice_date', '>=', $from],
    ['invoice_date', '<=', $to],
    ['rental_order_id.rental_type', '=', 'subscription'],
];

$allIds = $odoo->execute('rental.period.invoice', 'search', [$domain]);
$total = count($allIds);
echo "Total IDs: $total\n";

$exportFields = [
    'rental_order_id/rental_status',
    'price_unit',
    'invoice_id/payment_state',
    'invoice_id/name'
];

$chunkSize = 1000;
$chunks = array_chunk($allIds, $chunkSize);
$checked = 0;
$skippedPaidZero = 0;
$skippedPaidCancel = 0;
$actualPaid = 0;

foreach ($chunks as $chunk) {
    if ($checked >= 5000) break; // Check first 5000 records
    $result = $odoo->execute('rental.period.invoice', 'export_data', [$chunk, $exportFields]);
    foreach ($result['datas'] as $row) {
        $rentalStatus = $row[0] ?? '';
        $priceUnit = (float)($row[1] ?? 0);
        $payState = $row[2] ?? '';
        
        if ($payState === 'paid') {
            $actualPaid++;
            if ($priceUnit == 0) $skippedPaidZero++;
            if ($rentalStatus === 'cancel') $skippedPaidCancel++;
        }
    }
    $checked += count($chunk);
    echo "Checked $checked records...\n";
}

echo "Summary (First 5000 records):\n";
echo "Total Paid found: $actualPaid\n";
echo "Paid but price_unit == 0: $skippedPaidZero\n";
echo "Paid but rental_status == cancel: $skippedPaidCancel\n";
