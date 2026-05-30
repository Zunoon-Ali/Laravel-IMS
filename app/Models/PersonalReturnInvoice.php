<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalReturnInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'customer_name',
        'to_name',
        'date_returned',
        'description',
        'total_amount',
        'paid_amount',
        'due_amount',
        'notes',
    ];

    protected $casts = [
        'date_returned' => 'date:Y-m-d',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
    ];

    /**
     * Get the return items listed on this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PersonalReturnInvoiceItem::class, 'personal_return_invoice_id');
    }
}
