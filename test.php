<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $c = new \App\Http\Controllers\Api\PersonalNameController(
        app(\App\Services\PersonalService::class),
        app(\App\Repositories\Contracts\PersonalStockRepositoryInterface::class),
        app(\App\Repositories\Contracts\PersonalPaymentRepositoryInterface::class),
        app(\App\Repositories\Contracts\PersonalReturnRepositoryInterface::class),
        app(\App\Repositories\Contracts\PersonalSupplierRepositoryInterface::class),
        app(\App\Repositories\Contracts\PersonalCustomerRepositoryInterface::class),
        app(\App\Repositories\Contracts\PersonalPaymentSentRepositoryInterface::class)
    );
    $response = $c->getInvoiceItems('SAL-10001');
    echo "SUCCESS: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
