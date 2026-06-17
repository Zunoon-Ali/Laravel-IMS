<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Bank\StoreBankRequest;
use App\Http\Requests\Api\Bank\UpdateBankRequest;
use App\Http\Resources\BankResource;
use App\Repositories\Contracts\BankRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly BankRepositoryInterface $bankRepo
    ) {}

    /**
     * Get all Banks.
     */
    public function index(): JsonResponse
    {
        $banks = $this->bankRepo->all();
        return $this->successResponse(
            BankResource::collection($banks),
            'Banks retrieved successfully'
        );
    }

    /**
     * Store a new Bank.
     */
    public function store(StoreBankRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Check if bank with same name already exists
            $existingBank = $this->bankRepo->findByBankName($data['bankName']);
            if ($existingBank) {
                return $this->errorResponse('Bank with this name already exists', 409);
            }
            
            // Map request camelCase to DB snake_case
            $mappedData = [
                'bank_name' => $data['bankName'],
                'logo' => $data['logo'] ?? null,
                'account_number' => $data['accountNumber'] ?? null,
                'balance' => $data['balance'],
                'branch' => $data['branch'] ?? null,
            ];

            $bank = $this->bankRepo->create($mappedData);

            return $this->successResponse(
                new BankResource($bank),
                'Bank added successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Bank store failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to add bank: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing Bank.
     */
    public function update(UpdateBankRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $mappedData = [
                'bank_name' => $data['bankName'],
                'logo' => $data['logo'] ?? null,
                'account_number' => $data['accountNumber'] ?? null,
                'balance' => $data['balance'],
                'branch' => $data['branch'] ?? null,
            ];

            $bank = $this->bankRepo->update($id, $mappedData);

            if (!$bank) {
                return $this->errorResponse('Bank not found', 404);
            }

            return $this->successResponse(
                new BankResource($bank),
                'Bank updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Bank update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to update bank: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a Bank.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->bankRepo->delete($id);
            if (!$deleted) {
                return $this->errorResponse('Bank not found or failed to delete', 404);
            }
            return $this->successResponse(null, 'Bank deleted successfully');
        } catch (\Exception $e) {
            Log::error('Bank delete failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete bank: ' . $e->getMessage(), 500);
        }
    }
}
