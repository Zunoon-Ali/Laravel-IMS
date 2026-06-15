<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalCustomer;
use Illuminate\Database\Eloquent\Collection;

interface PersonalCustomerRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): PersonalCustomer;
    public function update(int|string $id, array $data): ?PersonalCustomer;
    public function delete(int|string $id): bool;
}
