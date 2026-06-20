<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalPaymentReceivedItem extends Model
{
    use HasFactory;

    protected $table = 'personal_payment_received_items';

    protected $fillable = [
        'personal_payment_received_id',
        'bale_type',
        'item_name',
        'company',
        'no_of_bales',
        'weight',
        'rate',
        'amount',
    ];

    protected $casts = [
        'no_of_bales' => 'integer',
        'weight' => 'float',
        'rate' => 'float',
        'amount' => 'float',
    ];

    /**
     * Get the parent payment received (Sale Invoice).
     */
    public function paymentReceived(): BelongsTo
    {
        return $this->belongsTo(PersonalPaymentReceived::class, 'personal_payment_received_id');
    }
}
