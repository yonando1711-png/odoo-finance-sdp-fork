<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$service = app(\App\Services\OdooService::class);
$fields = $service->execute('account.move.line', 'fields_get');
foreach ($fields as $name => $info) {
    if (strpos($name, 'start') !== false || strpos($name, 'rental') !== false || strpos($name, 'period') !== false) {
        echo "$name\n";
    }
}
