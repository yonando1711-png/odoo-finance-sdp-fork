<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\InvoiceSubscriptionController;
use Illuminate\Http\Request;

$controller = app(InvoiceSubscriptionController::class);
$request = new Request([
    'from' => '2025-04-01',
    'to'   => date('Y-m-d', strtotime('+15 days'))
]);

echo "Starting Deep Re-Sync...\n";
$response = $controller->sync($request, app(\App\Services\SyncService::class));
echo $response->getContent() . "\n";
