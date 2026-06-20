<?php

namespace App\Repositories\Contracts;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Collection;

interface BankRepositoryInterface
{
    public function all(): Collection;
    public function searchAndPaginate(array $filters);
    public function create(array $data): Bank;
    public function update(int|string $id, array $data): ?Bank;
    public function delete(int|string $id): bool;
    public function findByBankName(string $bankName, int|string $excludeId = null): ?Bank;
}
