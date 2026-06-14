<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Repositories\Contracts\BankRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BankRepository implements BankRepositoryInterface
{
    public function all(): Collection
    {
        return Bank::latest('id')->get();
    }

    public function create(array $data): Bank
    {
        return Bank::create($data);
    }

    public function update(int|string $id, array $data): ?Bank
    {
        $bank = Bank::find($id);
        if ($bank) {
            $bank->update($data);
            return $bank;
        }
        return null;
    }

    public function delete(int|string $id): bool
    {
        $bank = Bank::find($id);
        if ($bank) {
            return (bool) $bank->delete();
        }
        return false;
    }
}
