<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankLedger extends Model
{
    use HasFactory;

    protected $table = 'bank_ledger';

    protected $fillable = [
        'bank_id',
        'invoice_no',
        'transaction_type',
        'payment_type',
        'amount',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
        'transaction_date' => 'date:Y-m-d',
    ];

    /**
     * Get the bank that owns the ledger entry.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Scope to get only credit transactions.
     */
    public function scopeCredit($query)
    {
        return $query->where('transaction_type', 'credit');
    }

    /**
     * Scope to get only debit transactions.
     */
    public function scopeDebit($query)
    {
        return $query->where('transaction_type', 'debit');
    }
}
