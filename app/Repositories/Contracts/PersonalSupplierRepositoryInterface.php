<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalSupplier;
use Illuminate\Database\Eloquent\Collection;

interface PersonalSupplierRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): PersonalSupplier;
}
