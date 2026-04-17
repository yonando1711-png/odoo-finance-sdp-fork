<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$service = app(\App\Services\OdooService::class);
$moves = $service->execute('account.move', 'search', [[['name', '=', 'INVRS/2026/04058']]]);
$exportFields = ['name', 'invoice_line_ids/name', 'invoice_line_ids/start_rental_period', 'invoice_line_ids/end_rental_period'];
$result = $service->execute('account.move', 'export_data', [$moves, $exportFields]);
print_r($result);
