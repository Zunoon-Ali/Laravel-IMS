<?php

namespace App\Repositories\Eloquent;

use App\Models\PersonalPaymentSent;
use App\Repositories\Contracts\PersonalPaymentSentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonalPaymentSentRepository implements PersonalPaymentSentRepositoryInterface
{
    public function getAllWithRelations(): Collection
    {
        return PersonalPaymentSent::with(['cheques', 'onlines'])->orderBy('date_sent', 'desc')->orderBy('id', 'desc')->get();
    }

    public function create(array $data): PersonalPaymentSent
    {
        return PersonalPaymentSent::create($data);
    }

    public function getLatest(): ?PersonalPaymentSent
    {
        return PersonalPaymentSent::latest('id')->first();
    }
}
