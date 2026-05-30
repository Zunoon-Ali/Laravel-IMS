<?php

namespace App\Repositories\Contracts;

use App\Models\PersonalReturnInvoice;
use Illuminate\Database\Eloquent\Collection;

interface PersonalReturnRepositoryInterface
{
    public function getAllWithItems(): Collection;
    public function create(array $data): PersonalReturnInvoice;
}
