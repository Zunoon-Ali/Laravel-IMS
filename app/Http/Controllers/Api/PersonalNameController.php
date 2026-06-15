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
     * Delete a Personal Customer.
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
}
