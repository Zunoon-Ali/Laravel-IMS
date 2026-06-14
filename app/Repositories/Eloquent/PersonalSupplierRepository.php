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

    public function update(int|string $id, array $data): ?PersonalSupplier
    {
        $supplier = PersonalSupplier::find($id);
        if ($supplier) {
            $supplier->update($data);
            return $supplier;
        }
        return null;
    }

    public function delete(int|string $id): bool
    {
        $supplier = PersonalSupplier::find($id);
        if ($supplier) {
            return (bool) $supplier->delete();
        }
        return false;
    }
}
