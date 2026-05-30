<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalReturnInvoice;
use App\Repositories\Contracts\PersonalReturnRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalReturnRepository implements PersonalReturnRepositoryInterface
{
    public function getAllWithItems(): Collection
    {
        // Eager load items to prevent N+1 issues and sort by latest id
        return PersonalReturnInvoice::with('items')->latest('id')->get();
    }

    public function create(array $data): PersonalReturnInvoice
    {
        return PersonalReturnInvoice::create($data);
    }
}
