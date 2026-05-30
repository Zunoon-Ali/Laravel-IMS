<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Personal\StoreStockEntryRequest;
use App\Http\Requests\Api\Personal\StorePaymentReceivedRequest;
use App\Http\Requests\Api\Personal\StoreReturnInvoiceRequest;
use App\Http\Resources\PersonalStockEntryResource;
use App\Http\Resources\PersonalPaymentReceivedResource;
use App\Http\Resources\PersonalReturnInvoiceResource;
use App\DTOs\PersonalStockEntryDTO;
use App\DTOs\PersonalPaymentReceivedDTO;
use App\DTOs\PersonalReturnInvoiceDTO;
use App\Repositories\Contracts\PersonalStockRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentRepositoryInterface;
use App\Repositories\Contracts\PersonalReturnRepositoryInterface;
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
        protected readonly PersonalReturnRepositoryInterface $returnRepo
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
}
