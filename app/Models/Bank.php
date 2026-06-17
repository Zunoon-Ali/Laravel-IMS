<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'logo',
        'account_number',
        'balance',
        'branch',
    ];

    protected $casts = [
        'balance' => 'float',
    ];

    /**
     * Get the ledger entries for the bank.
     */
    public function ledger(): HasMany
    {
        return $this->hasMany(BankLedger::class);
    }

    /**
     * Get the current balance from ledger (more accurate than stored balance).
     */
    public function getCurrentBalanceAttribute(): float
    {
        $latestEntry = $this->ledger()->latest()->first();
        return $latestEntry ? $latestEntry->balance_after : (float) $this->balance;
    }
}
