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
}
