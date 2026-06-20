<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Repositories\Contracts\BankRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BankRepository implements BankRepositoryInterface
{
    public function all(): Collection
    {
        return Bank::where('status', 'Active')->latest('id')->get();
    }

    public function searchAndPaginate(array $filters)
    {
        $query = Bank::query();

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status']) && in_array($filters['status'], ['Active', 'Inactive'])) {
            $query->where('status', $filters['status']);
        }

        // Sort filter
        $sortBy = $filters['sort_by'] ?? 'bank_name';
        $sortOrder = $filters['sort_order'] ?? 'asc';

        if ($sortBy === 'current_balance') {
            $query->orderBy('current_balance', $sortOrder);
        } else {
            $query->orderBy('bank_name', $sortOrder);
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function create(array $data): Bank
    {
        // For new banks, we set opening_balance and current_balance from the incoming balance field
        return Bank::create($data);
    }

    public function update(int|string $id, array $data): ?Bank
    {
        $bank = Bank::find($id);
        if ($bank) {
            $oldOpening = (float) $bank->opening_balance;
            $bank->update($data);
            
            // If opening balance changed, recalculate ledger running balances
            if ($oldOpening !== (float) ($data['opening_balance'] ?? 0.00)) {
                $bank->recalculateBalance();
            }
            return $bank;
        }
        return null;
    }

    public function delete(int|string $id): bool
    {
        $bank = Bank::find($id);
        if ($bank) {
            return (bool) $bank->delete();
        }
        return false;
    }

    public function findByBankName(string $bankName, int|string $excludeId = null): ?Bank
    {
        $query = Bank::whereRaw('LOWER(bank_name) = ?', [strtolower($bankName)]);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->first();
    }
}
