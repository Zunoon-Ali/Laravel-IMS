<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalPaymentOnline extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_payment_received_id',
        'bank_name',
        'name',
        'payment_date',
        'from_name',
        'to_name',
        'amount',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'amount' => 'float',
    ];

    /**
     * Get the payment received record.
     */
    public function paymentReceived(): BelongsTo
    {
        return $this->belongsTo(PersonalPaymentReceived::class, 'personal_payment_received_id');
    }
}
