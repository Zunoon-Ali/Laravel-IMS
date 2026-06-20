<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContainerController;
use App\Http\Controllers\Api\PersonalNameController;
use App\Http\Controllers\Api\SmallBaleController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\AccountBalanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Routes
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/profile/image', [AuthController::class, 'uploadProfileImage']);

    // Container Routes
    Route::apiResource('containers', ContainerController::class);
    Route::get('/opened-bales', [ContainerController::class, 'getOpenedBales']);
    Route::post('/opened-bales', [ContainerController::class, 'storeOpenedBales']);
    Route::put('/opened-bales/{openedBale}', [ContainerController::class, 'updateOpenedBale']);
    Route::delete('/opened-bales/{openedBale}', [ContainerController::class, 'destroyOpenedBale']);

    // Small Bale Routes
    Route::get('/small-bales/daily-production', [SmallBaleController::class, 'getDailyProductions']);
    Route::get('/small-bales/daily-sales', [SmallBaleController::class, 'getDailySales']);
    Route::post('/small-bales/upload-image', [SmallBaleController::class, 'uploadImage']);
    Route::apiResource('small-bales', SmallBaleController::class);
    Route::post('/productions/batch', [SmallBaleController::class, 'storeProductionBatch']);

    // Bank Routes
    Route::patch('/banks/{id}/status', [BankController::class, 'toggleStatus']);
    Route::post('/banks/{id}/recalculate-balance', [BankController::class, 'recalculateBalance']);
    Route::apiResource('banks', BankController::class);

    // Personal Name Module Routes
    Route::prefix('personal')->group(function () {
        Route::get('/next-invoice', [PersonalNameController::class, 'getNextInvoiceNo']);
        Route::get('/next-stock-invoice', [PersonalNameController::class, 'getNextStockInvoiceNo']);
        Route::get('/stock-entries', [PersonalNameController::class, 'getStockEntries']);
        Route::post('/stock-entries', [PersonalNameController::class, 'storeStockEntry']);
        Route::put('/stock-entries/{id}', [PersonalNameController::class, 'updateStockEntry']);
        Route::delete('/stock-entries/{id}', [PersonalNameController::class, 'destroyStockEntry']);
        Route::get('/payments-received', [PersonalNameController::class, 'getPaymentsReceived']);
        Route::post('/payments-received', [PersonalNameController::class, 'storePaymentReceived']);
        Route::get('/return-invoices', [PersonalNameController::class, 'getReturnInvoices']);
        Route::post('/return-invoices', [PersonalNameController::class, 'storeReturnInvoice']);
        Route::get('/payments-sent', [PersonalNameController::class, 'getPaymentsSent']);
        Route::post('/payments-sent', [PersonalNameController::class, 'storePaymentSent']);

        Route::get('/suppliers', [PersonalNameController::class, 'getSuppliers']);
        Route::post('/suppliers', [PersonalNameController::class, 'storeSupplier']);
        Route::put('/suppliers/{id}', [PersonalNameController::class, 'updateSupplier']);
        Route::delete('/suppliers/{id}', [PersonalNameController::class, 'destroySupplier']);
        Route::get('/customers', [PersonalNameController::class, 'getCustomers']);
        Route::post('/customers', [PersonalNameController::class, 'storeCustomer']);
        Route::put('/customers/{id}', [PersonalNameController::class, 'updateCustomer']);
        Route::delete('/customers/{id}', [PersonalNameController::class, 'destroyCustomer']);
        Route::patch('/customers/{id}/deactivate', [PersonalNameController::class, 'deactivateCustomer']);
        Route::patch('/customers/{id}/activate', [PersonalNameController::class, 'activateCustomer']);
        Route::get('/customer-ledger/{customerId}', [PersonalNameController::class, 'getCustomerLedger']);
        Route::get('/invoice-items/{invoiceNo}', [PersonalNameController::class, 'getInvoiceItems']);
        Route::post('/customer-sale-invoices', [PersonalNameController::class, 'storeCustomerSaleInvoice']);
    });

    // My Account Balance Routes
    Route::prefix('account-balance')->group(function () {
        Route::get('/ledger/{bankId}', [AccountBalanceController::class, 'getBankLedger']);
        Route::get('/overview/{companyId?}', [AccountBalanceController::class, 'getOverview']);
        Route::get('/totals', [AccountBalanceController::class, 'getTotals']);
        Route::get('/payments', [AccountBalanceController::class, 'getPayments']);
        Route::get('/cheques', [AccountBalanceController::class, 'getCheques']);
        Route::get('/cash', [AccountBalanceController::class, 'getCash']);
        Route::get('/bank-cards', [AccountBalanceController::class, 'getBankCards']);
        Route::get('/bank-transactions', [AccountBalanceController::class, 'getBankTransactions']);
        Route::get('/payment/{invoiceNo}', [AccountBalanceController::class, 'getDetailedPayment']);
    });
});
