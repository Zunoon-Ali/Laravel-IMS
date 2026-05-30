<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalStockEntry;
use App\Repositories\Contracts\PersonalStockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalStockRepository implements PersonalStockRepositoryInterface
{
    public function getAllWithItems(): Collection
    {
        // Eager load items to prevent N+1 issues and sort by latest date_added/id
        return PersonalStockEntry::with('items')->latest('id')->get();
    }

    public function create(array $data): PersonalStockEntry
    {
        return PersonalStockEntry::create($data);
    }
}
