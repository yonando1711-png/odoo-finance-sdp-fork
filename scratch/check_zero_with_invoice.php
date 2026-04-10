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
    ['invoice_id', '!=', false],
];

$allIds = $odoo->execute('rental.period.invoice', 'search', [$domain]);
echo "Total IDs with invoices: " . count($allIds) . "\n";

$exportFields = [ 'invoice_id/name', 'price_unit', 'invoice_id/amount_total' ];
$chunk = array_slice($allIds, 0, 20);
$result = $odoo->execute('rental.period.invoice', 'export_data', [$chunk, $exportFields]);

print_r($result['datas']);
