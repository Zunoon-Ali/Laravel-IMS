<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalPaymentSentCheque extends Model
{
    use HasFactory;

    protected $table = 'personal_payment_sent_cheques';

    protected $fillable = [
        'personal_payment_sent_id',
        'bank_name',
        'check_no',
        'due_date',
        'to_name',
        'amount',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'amount' => 'float',
    ];

    /**
     * Get the payment sent record.
     */
    public function paymentSent(): BelongsTo
    {
        return $this->belongsTo(PersonalPaymentSent::class, 'personal_payment_sent_id');
    }
}
