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

$exportFields = [ 'price_unit', 'rental_order_id/rental_status' ];

$chunkSize = 1000;
$chunks = array_chunk($allIds, $chunkSize);
$zeroPrice = 0;
$cancelStatus = 0;
$both = 0;
$checked = 0;

foreach ($chunks as $chunk) {
    if ($checked >= 10000) break; 
    $result = $odoo->execute('rental.period.invoice', 'export_data', [$chunk, $exportFields]);
    foreach ($result['datas'] as $row) {
        $price = (float)($row[0] ?? 0);
        $status = $row[1] ?? '';
        
        $isZero = ($price == 0);
        $isCancel = ($status === 'cancel');
        
        if ($isZero) $zeroPrice++;
        if ($isCancel) $cancelStatus++;
        if ($isZero || $isCancel) $both++;
    }
    $checked += count($chunk);
    echo "Checked $checked...\n";
}

echo "Checked: $checked\n";
echo "Zero Price: $zeroPrice\n";
echo "Cancelled Status: $cancelStatus\n";
echo "Either: $both\n";
