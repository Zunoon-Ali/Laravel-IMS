<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalStockEntry;
use Illuminate\Database\Eloquent\Collection;

interface PersonalStockRepositoryInterface
{
    public function getAllWithItems(): Collection;
    public function create(array $data): PersonalStockEntry;
    public function update(int $id, array $data): ?PersonalStockEntry;
    public function delete(int $id): bool;
}
