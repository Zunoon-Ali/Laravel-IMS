<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Bank\StoreBankRequest;
use App\Http\Requests\Api\Bank\UpdateBankRequest;
use App\Http\Resources\BankResource;
use App\Repositories\Contracts\BankRepositoryInterface;
use App\Traits\ApiResponse;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly BankRepositoryInterface $bankRepo
    ) {}

    /**
     * Get all Banks (supports pagination/filtering if requested, else returns active banks).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('page') || $request->has('search') || $request->has('status') || $request->has('sort_by')) {
            $banks = $this->bankRepo->searchAndPaginate($request->all());
            return $this->successResponse([
                'data' => BankResource::collection($banks->items()),
                'meta' => [
                    'current_page' => $banks->currentPage(),
                    'last_page' => $banks->lastPage(),
                    'per_page' => $banks->perPage(),
                    'total' => $banks->total(),
                ]
            ], 'Banks retrieved successfully');
        }

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
            
            // Check if bank with same name already exists (case-insensitive)
            $existingBank = $this->bankRepo->findByBankName($data['bankName']);
            if ($existingBank) {
                return $this->errorResponse('Bank with this name already exists', 409);
            }
            
            // Map request camelCase to DB snake_case
            $mappedData = [
                'bank_name' => $data['bankName'],
                'logo' => $data['logo'] ?? null,
                'account_number' => $data['accountNumber'] ?? null,
                'opening_balance' => $data['balance'],
                'current_balance' => $data['balance'], // Initially current_balance = opening_balance
                'branch' => $data['branch'] ?? null,
                'status' => 'Active',
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

            // Check if bank with same name already exists under different ID (case-insensitive)
            $existingBank = $this->bankRepo->findByBankName($data['bankName'], $id);
            if ($existingBank) {
                return $this->errorResponse('Bank with this name already exists', 409);
            }

            $mappedData = [
                'bank_name' => $data['bankName'],
                'logo' => $data['logo'] ?? null,
                'account_number' => $data['accountNumber'] ?? null,
                'opening_balance' => $data['balance'],
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
            $bank = Bank::find($id);
            if (!$bank) {
                return $this->errorResponse('Bank not found', 404);
            }

            // Enforce soft delete validation: block if any ledger transaction exists
            if ($bank->ledger()->exists()) {
                return $this->errorResponse('This bank has existing transactions. You can deactivate it instead.', 400);
            }

            $deleted = $this->bankRepo->delete($id);
            if (!$deleted) {
                return $this->errorResponse('Failed to delete bank', 500);
            }
            return $this->successResponse(null, 'Bank deleted successfully');
        } catch (\Exception $e) {
            Log::error('Bank delete failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete bank: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle status (Activate/Deactivate) of a Bank.
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $bank = Bank::find($id);
            if (!$bank) {
                return $this->errorResponse('Bank not found', 404);
            }

            $newStatus = $bank->status === 'Active' ? 'Inactive' : 'Active';
            $bank->update(['status' => $newStatus]);

            return $this->successResponse(
                new BankResource($bank),
                "Bank status updated to {$newStatus} successfully"
            );
        } catch (\Exception $e) {
            Log::error('Bank status toggle failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to toggle bank status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Recalculate stored current_balance and running balances in ledger.
     */
    public function recalculateBalance($id): JsonResponse
    {
        try {
            $bank = Bank::find($id);
            if (!$bank) {
                return $this->errorResponse('Bank not found', 404);
            }

            $newBalance = $bank->recalculateBalance();

            return $this->successResponse([
                'currentBalance' => $newBalance,
                'realTimeBalance' => $bank->real_time_balance,
                'hasMismatch' => $bank->has_balance_mismatch,
            ], 'Bank ledger and balance recalculated successfully');
        } catch (\Exception $e) {
            Log::error('Bank recalculate balance failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to recalculate bank balance: ' . $e->getMessage(), 500);
        }
    }
}
