<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Personal\StoreStockEntryRequest;
use App\Http\Requests\Api\Personal\UpdateStockEntryRequest;
use App\Http\Requests\Api\Personal\StorePaymentReceivedRequest;
use App\Http\Requests\Api\Personal\StoreReturnInvoiceRequest;
use App\Http\Requests\Api\Personal\StoreSupplierRequest;
use App\Http\Requests\Api\Personal\UpdateSupplierRequest;
use App\Http\Requests\Api\Personal\StoreCustomerRequest;
use App\Http\Requests\Api\Personal\StorePaymentSentRequest;
use App\Http\Requests\Api\Personal\UpdateCustomerRequest;
use App\Http\Resources\PersonalStockEntryResource;
use App\Http\Resources\PersonalPaymentReceivedResource;
use App\Http\Resources\PersonalReturnInvoiceResource;
use App\Http\Resources\PersonalPaymentSentResource;
use App\Http\Resources\PersonalSupplierResource;
use App\Http\Resources\PersonalCustomerResource;
use App\DTOs\PersonalStockEntryDTO;
use App\DTOs\PersonalPaymentReceivedDTO;
use App\DTOs\PersonalReturnInvoiceDTO;
use App\DTOs\PersonalPaymentSentDTO;
use App\Repositories\Contracts\PersonalStockRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentRepositoryInterface;
use App\Repositories\Contracts\PersonalReturnRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentSentRepositoryInterface;
use App\Repositories\Contracts\PersonalSupplierRepositoryInterface;
use App\Repositories\Contracts\PersonalCustomerRepositoryInterface;
use App\Services\PersonalService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PersonalNameController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly PersonalService $personalService,
        protected readonly PersonalStockRepositoryInterface $stockRepo,
        protected readonly PersonalPaymentRepositoryInterface $paymentRepo,
        protected readonly PersonalReturnRepositoryInterface $returnRepo,
        protected readonly PersonalSupplierRepositoryInterface $supplierRepo,
        protected readonly PersonalCustomerRepositoryInterface $customerRepo,
        protected readonly PersonalPaymentSentRepositoryInterface $paymentSentRepo
    ) {}

    /**
     * Get next sequential Invoice Number.
     */
    public function getNextInvoiceNo(): JsonResponse
    {
        $invoiceNo = $this->personalService->generateNextInvoiceNo();
        return $this->successResponse([
            'invoice_no' => $invoiceNo
        ], 'Next invoice number generated');
    }

    /**
     * Get next sequential Stock Entry Invoice/Container Number.
     */
    public function getNextStockInvoiceNo(): JsonResponse
    {
        $invoiceNo = $this->personalService->generateNextStockInvoiceNo();
        return $this->successResponse([
            'invoice_no' => $invoiceNo
        ], 'Next stock invoice number generated');
    }

    /**
     * Get all Purchased Stock Entries.
     */
    public function getStockEntries(): JsonResponse
    {
        $entries = $this->stockRepo->getAllWithItems();
        return $this->successResponse(
            PersonalStockEntryResource::collection($entries),
            'Stock entries retrieved successfully'
        );
    }

    /**
     * Store a new Purchased Stock Entry with dynamic items.
     */
    public function storeStockEntry(StoreStockEntryRequest $request): JsonResponse
    {
        try {
            $dto = PersonalStockEntryDTO::fromRequest($request->validated());
            $entry = $this->personalService->storeStockEntry($dto);

            return $this->successResponse(
                new PersonalStockEntryResource($entry->load('items')),
                'Stock entry created successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Stock entry store failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to create stock entry: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing Purchased Stock Entry.
     */
    public function updateStockEntry(UpdateStockEntryRequest $request, $id): JsonResponse
    {
        try {
            $dto = PersonalStockEntryDTO::fromRequest($request->validated());
            $entry = $this->personalService->updateStockEntry((int) $id, $dto);

            if (!$entry) {
                return $this->errorResponse('Stock entry not found', 404);
            }

            return $this->successResponse(
                new PersonalStockEntryResource($entry->load('items')),
                'Stock entry updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Stock entry update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to update stock entry: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a Purchased Stock Entry.
     */
    public function destroyStockEntry($id): JsonResponse
    {
        try {
            $deleted = $this->personalService->deleteStockEntry((int) $id);
            if (!$deleted) {
                return $this->errorResponse('Stock entry not found or failed to delete', 404);
            }
            return $this->successResponse(null, 'Stock entry deleted successfully');
        } catch (\Exception $e) {
            Log::error('Stock entry delete failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete stock entry: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all Sales Invoices / Payments Received.
     */
    public function getPaymentsReceived(): JsonResponse
    {
        $payments = $this->paymentRepo->getAllWithRelations();
        return $this->successResponse(
            PersonalPaymentReceivedResource::collection($payments),
            'Payments received retrieved successfully'
        );
    }

    /**
     * Store a new Payment Received (Sales Invoice).
     */
    public function storePaymentReceived(StorePaymentReceivedRequest $request): JsonResponse
    {
        try {
            $dto = PersonalPaymentReceivedDTO::fromRequest($request->validated());
            $payment = $this->personalService->storePaymentReceived($dto);

            return $this->successResponse(
                new PersonalPaymentReceivedResource($payment->load(['cheques', 'onlines'])),
                'Payment received and Invoice generated successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Payment store failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to record payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all Return Invoices.
     */
    public function getReturnInvoices(): JsonResponse
    {
        $returns = $this->returnRepo->getAllWithItems();
        return $this->successResponse(
            PersonalReturnInvoiceResource::collection($returns),
            'Return invoices retrieved successfully'
        );
    }

    /**
     * Store a new Sell Return Invoice.
     */
    public function storeReturnInvoice(StoreReturnInvoiceRequest $request): JsonResponse
    {
        try {
            $dto = PersonalReturnInvoiceDTO::fromRequest($request->validated());
            $returnInvoice = $this->personalService->storeReturnInvoice($dto);

            return $this->successResponse(
                new PersonalReturnInvoiceResource($returnInvoice->load('items')),
                'Return invoice recorded successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Return invoice store failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to record return invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all Payments Sent.
     */
    public function getPaymentsSent(): JsonResponse
    {
        $payments = $this->paymentSentRepo->getAllWithRelations();
        return $this->successResponse(
            PersonalPaymentSentResource::collection($payments),
            'Payments sent retrieved successfully'
        );
    }

    /**
     * Store a new Payment Sent.
     */
    public function storePaymentSent(StorePaymentSentRequest $request): JsonResponse
    {
        try {
            $dto = PersonalPaymentSentDTO::fromRequest($request->validated());
            $payment = $this->personalService->storePaymentSent($dto);

            return $this->successResponse(
                new PersonalPaymentSentResource($payment->load(['cheques', 'onlines'])),
                'Payment sent recorded successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Payment sent store failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to record payment sent: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all Personal Suppliers.
     */
    public function getSuppliers(): JsonResponse
    {
        $suppliers = $this->supplierRepo->all();
        return $this->successResponse(
            PersonalSupplierResource::collection($suppliers),
            'Suppliers retrieved successfully'
        );
    }

    /**
     * Store a new Personal Supplier.
     */
    public function storeSupplier(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierRepo->create($request->validated());
        return $this->successResponse(
            new PersonalSupplierResource($supplier),
            'Supplier created successfully',
            201
        );
    }

    /**
     * Update an existing Personal Supplier.
     */
    public function updateSupplier(UpdateSupplierRequest $request, $id): JsonResponse
    {
        try {
            $supplier = $this->supplierRepo->update($id, $request->validated());
            if (!$supplier) {
                return $this->errorResponse('Supplier not found', 404);
            }
            return $this->successResponse(
                new PersonalSupplierResource($supplier),
                'Supplier updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Supplier update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to update supplier: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a Personal Supplier.
     */
    public function destroySupplier($id): JsonResponse
    {
        try {
            $deleted = $this->supplierRepo->delete($id);
            if (!$deleted) {
                return $this->errorResponse('Supplier not found or failed to delete', 404);
            }
            return $this->successResponse(null, 'Supplier deleted successfully');
        } catch (\Exception $e) {
            Log::error('Supplier delete failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete supplier: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all Personal Customers.
     */
    public function getCustomers(): JsonResponse
    {
        $customers = $this->customerRepo->all();
        return $this->successResponse(
            PersonalCustomerResource::collection($customers),
            'Customers retrieved successfully'
        );
    }

    /**
     * Store a new Personal Customer.
     */
    public function storeCustomer(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerRepo->create($request->validated());
        return $this->successResponse(
            new PersonalCustomerResource($customer),
            'Customer created successfully',
            201
        );
    }

    /**
     * Update an existing Personal Customer.
     */
    public function updateCustomer(UpdateCustomerRequest $request, $id): JsonResponse
    {
        try {
            $customer = $this->customerRepo->update($id, $request->validated());
            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }
            return $this->successResponse(
                new PersonalCustomerResource($customer),
                'Customer updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to update customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a Personal Customer (soft delete).
     */
    public function destroyCustomer($id): JsonResponse
    {
        try {
            $deleted = $this->customerRepo->delete($id);
            if (!$deleted) {
                return $this->errorResponse('Customer not found or failed to delete', 404);
            }
            return $this->successResponse(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            Log::error('Customer delete failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Deactivate a Customer.
     */
    public function deactivateCustomer($id): JsonResponse
    {
        try {
            $customer = $this->customerRepo->update($id, ['status' => 'Inactive']);
            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }
            return $this->successResponse(
                new PersonalCustomerResource($customer),
                'Customer deactivated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Customer deactivate failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to deactivate customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Activate a Customer.
     */
    public function activateCustomer($id): JsonResponse
    {
        try {
            $customer = $this->customerRepo->update($id, ['status' => 'Active']);
            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }
            return $this->successResponse(
                new PersonalCustomerResource($customer),
                'Customer activated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Customer activate failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to activate customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Customer Ledger with running balance calculation and pagination.
     */
    public function getCustomerLedger($customerId): JsonResponse
    {
        try {
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 15);
            $search = request()->get('search', '');

            // Get customer by ID
            $customer = \App\Models\PersonalCustomer::find($customerId);
            if (!$customer) {
                return $this->errorResponse('Customer not found', 404);
            }

            $customerName = $customer->name;

            // Get all transactions for the customer
            $paymentsReceived = \App\Models\PersonalPaymentReceived::where('customer_name', $customerName)
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with(['cheques', 'onlines'])
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'date' => optional($p->date_received)->format('d-M-Y') ?? $p->date_received,
                        'invoice_no' => $p->invoice_no,
                        'customer_name' => $p->customer_name,
                        'description' => $p->description ?? 'Payment Received',
                        'supplier_name' => null,
                        'big_bales' => 0,
                        'small_bales' => 0,
                        'weight' => 0,
                        'rate' => 0,
                        'debit' => (float) $p->total_amount,
                        'credit' => 0,
                        'type' => 'sale_invoice',
                    ];
                });

            $paymentsSent = \App\Models\PersonalPaymentSent::where('customer_name', $customerName)
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with(['cheques', 'onlines'])
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id + 100000,
                        'date' => optional($p->date_sent)->format('d-M-Y') ?? $p->date_sent,
                        'invoice_no' => $p->invoice_no,
                        'customer_name' => $p->customer_name,
                        'description' => $p->description ?? 'Payment Sent',
                        'supplier_name' => null,
                        'big_bales' => 0,
                        'small_bales' => 0,
                        'weight' => 0,
                        'rate' => 0,
                        'debit' => 0,
                        'credit' => (float) $p->total_amount,
                        'type' => 'payment_sent',
                    ];
                });

            $returnInvoices = \App\Models\PersonalReturnInvoice::where('customer_name', $customerName)
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with('items')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id + 200000,
                        'date' => optional($r->date)->format('d-M-Y') ?? $r->date,
                        'invoice_no' => $r->invoice_no,
                        'customer_name' => $r->customer_name,
                        'description' => $r->description ?? 'Return Invoice',
                        'supplier_name' => null,
                        'big_bales' => 0,
                        'small_bales' => 0,
                        'weight' => 0,
                        'rate' => 0,
                        'debit' => 0,
                        'credit' => (float) $r->total_amount,
                        'type' => 'return_invoice',
                    ];
                });

            // Merge all transactions and sort by date
            $allTransactions = $paymentsReceived->concat($paymentsSent)->concat($returnInvoices)
                ->sortByDesc('id')
                ->values();

            // Calculate running balance
            $runningBalance = 0;
            $transactionsWithBalance = $allTransactions->map(function ($tx) use (&$runningBalance) {
                $runningBalance += ($tx['debit'] - $tx['credit']);
                $tx['balance'] = $runningBalance;
                return $tx;
            });

            // Server-side pagination
            $total = $transactionsWithBalance->count();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedTransactions = $transactionsWithBalance->slice($offset, $perPage)->values();

            return $this->successResponse([
                'data' => $paginatedTransactions,
                'meta' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => (int) $totalPages,
                ]
            ], 'Customer ledger retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Customer ledger retrieval failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve customer ledger: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Invoice Items for a specific invoice (Screen 3 data).
     */
    public function getInvoiceItems($invoiceNo): JsonResponse
    {
        try {
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 15);
            $search = request()->get('search', '');

            // Try to find the invoice in payments received
            $paymentReceived = \App\Models\PersonalPaymentReceived::with(['cheques', 'onlines'])
                ->where('invoice_no', $invoiceNo)
                ->first();

            if ($paymentReceived) {
                $customer = \App\Models\PersonalCustomer::where('name', $paymentReceived->customer_name)->first();
                
                // Since payments received doesn't have items table, return empty or mock structure
                // In real implementation, this would come from invoice_items table
                $items = collect([
                    [
                        'id' => 1,
                        'item_name' => 'Sample Item',
                        'description' => $paymentReceived->description ?? 'Payment Received',
                        'weight' => 0,
                        'rate' => 0,
                        'big_bales' => 0,
                        'small_bales' => 0,
                        'total_no_of_bales' => 0,
                        'line_balance' => (float) $paymentReceived->total_amount,
                    ]
                ]);

                $total = $items->count();
                $totalPages = ceil($total / $perPage);
                $offset = ($page - 1) * $perPage;
                $paginatedItems = $items->slice($offset, $perPage)->values();

                return $this->successResponse([
                    'data' => $paginatedItems,
                    'meta' => [
                        'page' => (int) $page,
                        'per_page' => (int) $perPage,
                        'total' => $total,
                        'total_pages' => (int) $totalPages,
                    ],
                    'invoice' => [
                        'invoice_no' => $paymentReceived->invoice_no,
                        'date' => optional($paymentReceived->date_received)->format('d-M-Y') ?? $paymentReceived->date_received,
                        'description' => $paymentReceived->description,
                        'customer_name' => $paymentReceived->customer_name,
                        'total_amount' => (float) $paymentReceived->total_amount,
                    ]
                ], 'Invoice items retrieved successfully');
            }

            // Try to find in return invoices
            $returnInvoice = \App\Models\PersonalReturnInvoice::with('items')
                ->where('invoice_no', $invoiceNo)
                ->first();

            if ($returnInvoice) {
                $items = $returnInvoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'description' => 'Return Item',
                        'weight' => 0,
                        'rate' => 0,
                        'big_bales' => $item->is_big_bales ?? 0,
                        'small_bales' => $item->is_small_bales ?? 0,
                        'total_no_of_bales' => $item->no_of_bales ?? 0,
                        'line_balance' => (float) ($item->amount ?? 0),
                    ];
                });

                $total = $items->count();
                $totalPages = ceil($total / $perPage);
                $offset = ($page - 1) * $perPage;
                $paginatedItems = $items->slice($offset, $perPage)->values();

                return $this->successResponse([
                    'data' => $paginatedItems,
                    'meta' => [
                        'page' => (int) $page,
                        'per_page' => (int) $perPage,
                        'total' => $total,
                        'total_pages' => (int) $totalPages,
                    ],
                    'invoice' => [
                        'invoice_no' => $returnInvoice->invoice_no,
                        'date' => optional($returnInvoice->date)->format('d-M-Y') ?? $returnInvoice->date,
                        'description' => $returnInvoice->description,
                        'customer_name' => $returnInvoice->customer_name,
                        'total_amount' => (float) $returnInvoice->total_amount,
                    ]
                ], 'Invoice items retrieved successfully');
            }

            return $this->errorResponse('Invoice not found', 404);
        } catch (\Exception $e) {
            Log::error('Invoice items retrieval failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve invoice items: ' . $e->getMessage(), 500);
        }
    }
}
