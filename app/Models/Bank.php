<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_name',
        'logo',
        'account_number',
        'opening_balance',
        'current_balance',
        'status',
        'branch',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'current_balance' => 'float',
    ];

    /**
     * Get the ledger entries for the bank.
     */
    public function ledger(): HasMany
    {
        return $this->hasMany(BankLedger::class);
    }

    /**
     * Get the current balance from stored value.
     */
    public function getCurrentBalanceAttribute(): float
    {
        return (float) ($this->attributes['current_balance'] ?? 0.00);
    }

    /**
     * Calculate balance in real-time from transactions.
     */
    public function getRealTimeBalanceAttribute(): float
    {
        $credits = (float) $this->ledger()->where('transaction_type', 'credit')->sum('amount');
        $debits = (float) $this->ledger()->where('transaction_type', 'debit')->sum('amount');
        return (float) $this->opening_balance + $credits - $debits;
    }

    /**
     * Check if stored current_balance mismatches real-time calculated balance.
     */
    public function getHasBalanceMismatchAttribute(): bool
    {
        return abs($this->current_balance - $this->real_time_balance) > 0.01;
    }

    /**
     * Recalculate ledger running balances and update stored current_balance.
     */
    public function recalculateBalance(): float
    {
        $opening = (float) $this->opening_balance;
        $running = $opening;

        // Retrieve ledger entries in chronological order
        $ledgerEntries = $this->ledger()
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($ledgerEntries as $entry) {
            if ($entry->transaction_type === 'credit') {
                $running += (float) $entry->amount;
            } else {
                $running -= (float) $entry->amount;
            }
            $entry->updateQuietly(['balance_after' => $running]);
        }

        $this->update(['current_balance' => $running]);

        return $running;
    }
}

