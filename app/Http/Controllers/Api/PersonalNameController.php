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
     * Get next sequential Payment Received Invoice Number (PAR- prefix).
     */
    public function getNextPaymentReceivedInvoiceNo(): JsonResponse
    {
        $invoiceNo = $this->personalService->generateNextPaymentReceivedInvoiceNo();
        return $this->successResponse([
            'invoice_no' => $invoiceNo
        ], 'Next payment received invoice number generated');
    }

    /**
     * Get next sequential Payment Sent Invoice Number (PAS- prefix).
     */
    public function getNextPaymentSentInvoiceNo(): JsonResponse
    {
        $invoiceNo = $this->personalService->generateNextPaymentSentInvoiceNo();
        return $this->successResponse([
            'invoice_no' => $invoiceNo
        ], 'Next payment sent invoice number generated');
    }

    /**
     * Get next sequential Sale Return Invoice Number (SRI- prefix).
     */
    public function getNextReturnInvoiceNo(): JsonResponse
    {
        $invoiceNo = $this->personalService->generateNextReturnInvoiceNo();
        return $this->successResponse([
            'invoice_no' => $invoiceNo
        ], 'Next return invoice number generated');
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
            $paymentsReceived = \App\Models\PersonalPaymentReceived::where(function ($query) use ($customerId, $customerName) {
                    $query->where('customer_id', $customerId)
                          ->orWhere('customer_name', $customerName);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with(['cheques', 'onlines', 'items'])
                ->get()
                ->map(function ($p) {
                    $isSal = str_starts_with($p->invoice_no, 'SAL');
                    
                    // Sum big bales, small bales, weight and compute rate
                    $bigBales = $p->items ? $p->items->where('bale_type', 'big')->sum('no_of_bales') : 0;
                    $smallBales = $p->items ? $p->items->where('bale_type', 'small')->sum('no_of_bales') : 0;
                    $weight = $p->items ? $p->items->sum('weight') : 0;
                    $rate = ($p->items && $p->items->first()) ? (float) $p->items->first()->rate : 0;

                    return [
                        'id' => $p->id,
                        'raw_date' => $p->date_received ? $p->date_received->format('Y-m-d') : '1970-01-01',
                        'raw_id' => $p->id,
                        'date' => optional($p->date_received)->format('d-M-Y') ?? $p->date_received,
                        'invoiceNo' => $p->invoice_no,
                        'customerName' => $p->customer_name,
                        'description' => $p->description ?? ($isSal ? 'Sale Invoice' : 'Payment Received'),
                        'supplierName' => $p->to_name,
                        'bigBales' => $bigBales,
                        'smallBales' => $smallBales,
                        'weightKgs' => $weight,
                        'rate' => $rate,
                        'debit' => $isSal ? (float) $p->total_amount : 0.0,
                        'credit' => !$isSal ? (float) $p->total_amount : 0.0,
                        'type' => $isSal ? 'sale_invoice' : 'payment_received',
                    ];
                });

            $paymentsSent = \App\Models\PersonalPaymentSent::where(function ($query) use ($customerId, $customerName) {
                    $query->where('customer_id', $customerId)
                          ->orWhere('customer_name', $customerName);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with(['cheques', 'onlines'])
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id + 100000,
                        'raw_date' => $p->date_sent ? $p->date_sent->format('Y-m-d') : '1970-01-01',
                        'raw_id' => $p->id,
                        'date' => optional($p->date_sent)->format('d-M-Y') ?? $p->date_sent,
                        'invoiceNo' => $p->invoice_no,
                        'customerName' => $p->customer_name,
                        'description' => $p->description ?? 'Payment Sent',
                        'supplierName' => $p->to_name,
                        'bigBales' => 0,
                        'smallBales' => 0,
                        'weightKgs' => 0,
                        'rate' => 0,
                        'debit' => (float) $p->total_amount,
                        'credit' => 0.0,
                        'type' => 'payment_sent',
                    ];
                });

            $returnInvoices = \App\Models\PersonalReturnInvoice::where(function ($query) use ($customerId, $customerName) {
                    $query->where('customer_id', $customerId)
                          ->orWhere('customer_name', $customerName);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                })
                ->with('items')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id + 200000,
                        'raw_date' => $r->date_returned ? $r->date_returned->format('Y-m-d') : '1970-01-01',
                        'raw_id' => $r->id,
                        'date' => optional($r->date_returned)->format('d-M-Y') ?? $r->date_returned,
                        'invoiceNo' => $r->invoice_no,
                        'customerName' => $r->customer_name,
                        'description' => $r->description ?? 'Return Invoice',
                        'supplierName' => $r->to_name,
                        'bigBales' => $r->items ? $r->items->where('is_big_bales', true)->sum('no_of_bales') : 0,
                        'smallBales' => $r->items ? $r->items->where('is_small_bales', true)->sum('no_of_bales') : 0,
                        'weightKgs' => 0,
                        'rate' => 0,
                        'debit' => 0.0,
                        'credit' => (float) $r->total_amount,
                        'type' => 'return_invoice',
                    ];
                });

            // Merge all transactions and sort by date ASC (chronological) for running balance
            $allTransactionsSortedAsc = $paymentsReceived->concat($paymentsSent)->concat($returnInvoices)
                ->sort(function ($a, $b) {
                    $dateCompare = strcmp($a['raw_date'], $b['raw_date']);
                    if ($dateCompare !== 0) {
                        return $dateCompare;
                    }
                    return $a['raw_id'] <=> $b['raw_id'];
                })
                ->values();

            // Calculate running balance in ascending chronological order
            $runningBalance = 0;
            $transactionsWithBalance = $allTransactionsSortedAsc->map(function ($tx) use (&$runningBalance) {
                // debit is plus, credit is minus
                $runningBalance += ($tx['debit'] - $tx['credit']);
                $tx['balance'] = $runningBalance;
                return $tx;
            });

            // Re-sort to DESC (newest first) for paginated presentation
            $transactionsWithBalance = $transactionsWithBalance->reverse()->values();

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
            $paymentReceived = \App\Models\PersonalPaymentReceived::with(['cheques', 'onlines', 'items'])
                ->where('invoice_no', $invoiceNo)
                ->first();

            if ($paymentReceived) {
                $items = $paymentReceived->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_name' => $item->item_name,
                        'description' => $item->bale_type === 'small' ? 'Small Bales Item' : 'Big Bales Item',
                        'weight' => (float) $item->weight,
                        'rate' => (float) $item->rate,
                        'big_bales' => $item->bale_type === 'big' ? $item->no_of_bales : 0,
                        'small_bales' => $item->bale_type === 'small' ? $item->no_of_bales : 0,
                        'total_no_of_bales' => (int) $item->no_of_bales,
                        'line_balance' => (float) $item->amount,
                    ];
                });

                // If items are empty, but the invoice exists, return a default cash/payment row
                if ($items->isEmpty()) {
                    $items = collect([
                        [
                            'id' => 1,
                            'item_name' => 'Payment Received',
                            'description' => $paymentReceived->description ?? 'Payment Received via Cash/Cheque/Online',
                            'weight' => 0.0,
                            'rate' => 0.0,
                            'big_bales' => 0,
                            'small_bales' => 0,
                            'total_no_of_bales' => 0,
                            'line_balance' => (float) $paymentReceived->total_amount,
                        ]
                    ]);
                }

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
                        'date' => optional($returnInvoice->date_returned)->format('d-M-Y') ?? $returnInvoice->date_returned,
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

    /**
     * Store Customer Sale Invoice (SAL) with items and deduct stock.
     */
    public function storeCustomerSaleInvoice(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                // customerId is optional – allows custom/unknown names typed in the "To" dropdown
                'customerId' => 'nullable|integer|exists:personal_customers,id',
                'customerName' => 'required|string|max:255',
                'invoiceNo' => 'required|string',
                'supplierName' => 'nullable|string|max:255',
                'dateAdded' => 'nullable|string',
                'description' => 'nullable|string',
                'extraCharges' => 'nullable|numeric',
                'smallBaleItems' => 'nullable|array',
                'bigBaleItems' => 'nullable|array',
                'totalAmountPayable' => 'required|numeric',
            ]);

            $payment = $this->personalService->storeCustomerSaleInvoice($validated);

            return $this->successResponse(
                new PersonalPaymentReceivedResource($payment->load(['cheques', 'onlines', 'items'])),
                'Customer Sale Invoice generated successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Customer sale invoice store failed: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Get detailed Payment Received History breakdown.
     */
    public function getPaymentHistory($invoiceNo): JsonResponse
    {
        try {
            $payment = \App\Models\PersonalPaymentReceived::with(['cheques', 'onlines'])
                ->where('invoice_no', $invoiceNo)
                ->first();

            if (!$payment) {
                return $this->errorResponse('Invoice not found', 404);
            }

            $entries = collect();

            if ((float) $payment->cash_amount > 0) {
                $entries->push([
                    'id' => 'cash-' . $payment->id,
                    'date' => optional($payment->date_received)->format('d-M-Y') ?? $payment->date_received,
                    'paymentMode' => 'Cash',
                    'chequeNoOrTrId' => '—',
                    'amount' => (float) $payment->cash_amount,
                ]);
            }

            foreach ($payment->cheques as $cheque) {
                $entries->push([
                    'id' => 'cheque-' . $cheque->id,
                    'date' => optional($payment->date_received)->format('d-M-Y') ?? $payment->date_received,
                    'paymentMode' => 'Cheque',
                    'chequeNoOrTrId' => $cheque->check_no,
                    'amount' => (float) $cheque->amount,
                ]);
            }

            foreach ($payment->onlines as $online) {
                $entries->push([
                    'id' => 'online-' . $online->id,
                    'date' => optional($payment->date_received)->format('d-M-Y') ?? $payment->date_received,
                    'paymentMode' => 'Online',
                    'chequeNoOrTrId' => $online->name,
                    'amount' => (float) $online->amount,
                ]);
            }

            return $this->successResponse([
                'invoiceNo' => $payment->invoice_no,
                'date' => optional($payment->date_received)->format('d-M-Y') ?? $payment->date_received,
                'from' => $payment->customer_name,
                'to' => $payment->to_name,
                'description' => $payment->description ?? 'Payment Received',
                'totalAmountReceived' => (float) $payment->total_amount,
                'raw' => $payment,
                'entries' => $entries,
            ], 'Payment history retrieved successfully');
        } catch (\Exception $e) {
            Log::error('getPaymentHistory failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payment history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get detailed Payment Sent History breakdown.
     */
    public function getPaymentSentHistory($invoiceNo): JsonResponse
    {
        try {
            $payment = \App\Models\PersonalPaymentSent::with(['cheques', 'onlines'])
                ->where('invoice_no', $invoiceNo)
                ->first();

            if (!$payment) {
                return $this->errorResponse('Invoice not found', 404);
            }

            $entries = collect();

            if ((float) $payment->cash_amount > 0) {
                $entries->push([
                    'id' => 'cash-' . $payment->id,
                    'date' => optional($payment->date_sent)->format('d-M-Y') ?? $payment->date_sent,
                    'paymentMode' => 'Cash',
                    'chequeNoOrTrId' => '—',
                    'amount' => (float) $payment->cash_amount,
                ]);
            }

            foreach ($payment->cheques as $cheque) {
                $entries->push([
                    'id' => 'cheque-' . $cheque->id,
                    'date' => optional($payment->date_sent)->format('d-M-Y') ?? $payment->date_sent,
                    'paymentMode' => 'Cheque',
                    'chequeNoOrTrId' => $cheque->check_no,
                    'amount' => (float) $cheque->amount,
                ]);
            }

            foreach ($payment->onlines as $online) {
                $entries->push([
                    'id' => 'online-' . $online->id,
                    'date' => optional($payment->date_sent)->format('d-M-Y') ?? $payment->date_sent,
                    'paymentMode' => 'Online',
                    'chequeNoOrTrId' => $online->name,
                    'amount' => (float) $online->amount,
                ]);
            }

            return $this->successResponse([
                'invoiceNo' => $payment->invoice_no,
                'date' => optional($payment->date_sent)->format('d-M-Y') ?? $payment->date_sent,
                'from' => $payment->customer_name,
                'to' => $payment->to_name,
                'description' => $payment->description ?? 'Payment Sent',
                'totalAmountReceived' => (float) $payment->total_amount,
                'raw' => $payment,
                'entries' => $entries,
            ], 'Payment sent history retrieved successfully');
        } catch (\Exception $e) {
            Log::error('getPaymentSentHistory failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payment sent history: ' . $e->getMessage(), 500);
        }
    }
}
