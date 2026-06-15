<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalCustomer;
use App\Repositories\Contracts\PersonalCustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalCustomerRepository implements PersonalCustomerRepositoryInterface
{
    public function all(): Collection
    {
        return PersonalCustomer::latest('id')->get();
    }

    public function create(array $data): PersonalCustomer
    {
        return PersonalCustomer::create($data);
    }

    public function update(int|string $id, array $data): ?PersonalCustomer
    {
        $customer = PersonalCustomer::find($id);
        if ($customer) {
            $customer->update($data);
            return $customer;
        }
        return null;
    }

    public function delete(int|string $id): bool
    {
        $customer = PersonalCustomer::find($id);
        if ($customer) {
            return (bool) $customer->delete();
        }
        return false;
    }
}
