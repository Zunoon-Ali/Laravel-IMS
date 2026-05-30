<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalReturnInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_return_invoice_id',
        'item_name',
        'is_small_bales',
        'is_big_bales',
        'no_of_bales',
        'amount',
    ];

    protected $casts = [
        'is_small_bales' => 'boolean',
        'is_big_bales' => 'boolean',
        'no_of_bales' => 'integer',
        'amount' => 'float',
    ];

    /**
     * Get the parent return invoice.
     */
    public function returnInvoice(): BelongsTo
    {
        return $this->belongsTo(PersonalReturnInvoice::class, 'personal_return_invoice_id');
    }
}
