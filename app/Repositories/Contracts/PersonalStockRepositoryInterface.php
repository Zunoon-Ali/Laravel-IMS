<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalStockEntry;
use Illuminate\Database\Eloquent\Collection;

interface PersonalStockRepositoryInterface
{
    public function getAllWithItems(): Collection;
    public function create(array $data): PersonalStockEntry;
}
