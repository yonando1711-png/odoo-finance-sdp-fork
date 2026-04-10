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

echo "Querying rental.period.invoice for the range $from to $to...\n";

$allIds = $odoo->execute('rental.period.invoice', 'search', [$domain]);
echo "Total IDs found: " . count($allIds) . "\n";

if (empty($allIds)) exit;

$exportFields = [
    'rental_order_id/rental_status',
    'price_unit',
    'invoice_id/payment_state',
    'invoice_id/state',
    'invoice_id/name'
];

$chunk = array_slice($allIds, 0, 500);
$result = $odoo->execute('rental.period.invoice', 'export_data', [$chunk, $exportFields]);

$stats = [
    'rental_status' => [],
    'price_unit_zero' => 0,
    'price_unit_nonzero' => 0,
    'paid_count' => 0,
    'paid_with_zero_price' => 0,
];

foreach ($result['datas'] as $row) {
    $status = $row[0] ?? 'unknown';
    $price = (float)($row[1] ?? 0);
    $payState = $row[2] ?? '';
    
    $stats['rental_status'][$status] = ($stats['rental_status'][$status] ?? 0) + 1;
    if ($price == 0) $stats['price_unit_zero']++;
    else $stats['price_unit_nonzero']++;
    
    if ($payState === 'paid') {
        $stats['paid_count']++;
        if ($price == 0) $stats['paid_with_zero_price']++;
    }
}

print_r($stats);
