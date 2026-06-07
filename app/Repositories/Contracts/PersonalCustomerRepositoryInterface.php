<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalCustomer;
use Illuminate\Database\Eloquent\Collection;

interface PersonalCustomerRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): PersonalCustomer;
}
