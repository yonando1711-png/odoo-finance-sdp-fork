<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$service = app(\App\Services\OdooService::class);
$moves = $service->execute('account.move', 'search_read', [[['name', '=', 'INVRS/2026/04058']]], ['fields' => ['id', 'name', 'invoice_line_ids']]);
if (empty($moves)) { echo "Invoice not found\n"; exit; }
$lineIds = $moves[0]['invoice_line_ids'];
$lines = $service->execute('account.move.line', 'read', [$lineIds], ['fields' => ['id', 'name', 'start_rental_period', 'end_rental_period', 'actual_start_rental', 'actual_end_rental']]);
foreach ($lines as $line) {
    if (strpos($line['name'], 'TYT') !== false) {
        print_r($line);
    }
}
