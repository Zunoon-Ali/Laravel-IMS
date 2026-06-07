<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalSupplier;
use App\Repositories\Contracts\PersonalSupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalSupplierRepository implements PersonalSupplierRepositoryInterface
{
    public function all(): Collection
    {
        return PersonalSupplier::latest('id')->get();
    }

    public function create(array $data): PersonalSupplier
    {
        return PersonalSupplier::create($data);
    }
}
