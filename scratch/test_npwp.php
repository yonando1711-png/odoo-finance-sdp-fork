<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OdooService;

$odoo = new OdooService();
// We need to mock or provide dates. 
// Actually, let's just use the name directly for a search_read test in a new method.
// Or just run the existing fetch and filter.

$dateFrom = '2026-01-01';
$dateTo = '2026-12-31';
$result = $odoo->fetchInvoiceOthers($dateFrom, $dateTo);

if ($result['success']) {
    foreach ($result['data'] as $invoice) {
        if ($invoice['name'] === 'INVOW/2026/00228') {
             echo "Invoice: " . $invoice['name'] . PHP_EOL;
             echo "NPWP: [" . $invoice['partner_npwp'] . "]" . PHP_EOL;
             break;
        }
    }
} else {
    echo "Error: " . $result['message'] . PHP_EOL;
}
