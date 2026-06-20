<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalPaymentSent extends Model
{
    use HasFactory;

    protected $table = 'personal_payments_sent';

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'customer_name',
        'to_name',
        'date_sent',
        'cash_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'date_sent' => 'date:Y-m-d',
        'cash_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
    ];

    /**
     * Get the bank cheque details sent.
     */
    public function cheques(): HasMany
    {
        return $this->hasMany(PersonalPaymentSentCheque::class, 'personal_payment_sent_id');
    }

    /**
     * Get the online bank transfer details sent.
     */
    public function onlines(): HasMany
    {
        return $this->hasMany(PersonalPaymentSentOnline::class, 'personal_payment_sent_id');
    }
}
