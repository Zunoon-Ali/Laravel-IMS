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
        return PersonalStockEntry::with('items')->orderBy('date_added', 'desc')->orderBy('id', 'desc')->get();
    }

    public function create(array $data): PersonalStockEntry
    {
        return PersonalStockEntry::create($data);
    }

    public function update(int $id, array $data): ?PersonalStockEntry
    {
        $entry = PersonalStockEntry::find($id);
        if ($entry) {
            $entry->update($data);
            return $entry;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $entry = PersonalStockEntry::find($id);
        if ($entry) {
            return (bool) $entry->delete();
        }
        return false;
    }
}
