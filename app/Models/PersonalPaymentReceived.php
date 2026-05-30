<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalPaymentReceived extends Model
{
    use HasFactory;

    protected $table = 'personal_payments_received';

    protected $fillable = [
        'invoice_no',
        'customer_name',
        'to_name',
        'date_received',
        'cash_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'date_received' => 'date:Y-m-d',
        'cash_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
    ];

    /**
     * Get the bank cheque details received for this invoice.
     */
    public function cheques(): HasMany
    {
        return $this->hasMany(PersonalPaymentCheque::class, 'personal_payment_received_id');
    }

    /**
     * Get the online bank transfer details received for this invoice.
     */
    public function onlines(): HasMany
    {
        return $this->hasMany(PersonalPaymentOnline::class, 'personal_payment_received_id');
    }
}
