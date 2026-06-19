<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'status',
        'city',
        'address',
        'opening_balance',
        'balance_type',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    // Calculate current balance from transaction history
    public function getCurrentBalanceAttribute(): float
    {
        $openingBalance = $this->opening_balance ?? 0;
        $balanceType = $this->balance_type ?? 'debit';
        
        // Get all transactions for this customer
        $paymentsReceived = $this->paymentsReceived()->sum('amount');
        $paymentsSent = $this->paymentsSent()->sum('amount');
        $saleInvoices = $this->saleInvoices()->sum('total_amount');
        $returnInvoices = $this->returnInvoices()->sum('total_amount');
        
        // Calculate current balance
        // Debit: Customer owes us (positive balance)
        // Credit: We owe customer (negative balance)
        $currentBalance = $openingBalance;
        
        if ($balanceType === 'debit') {
            // Opening balance is receivable (positive)
            $currentBalance += $saleInvoices; // Sales increase what they owe
            $currentBalance -= $paymentsReceived; // Payments reduce what they owe
            $currentBalance -= $returnInvoices; // Returns reduce what they owe
        } else {
            // Opening balance is payable (negative)
            $currentBalance = -$openingBalance;
            $currentBalance -= $saleInvoices; // Sales increase what we owe
            $currentBalance += $paymentsReceived; // Payments reduce what we owe
            $currentBalance += $returnInvoices; // Returns reduce what we owe
        }
        
        return $currentBalance;
    }

    // Relationships
    public function paymentsReceived()
    {
        return $this->hasMany(\App\Models\PersonalPaymentReceived::class, 'customer_id');
    }

    public function paymentsSent()
    {
        return $this->hasMany(\App\Models\PersonalPaymentSent::class, 'customer_id');
    }

    public function saleInvoices()
    {
        return $this->hasMany(\App\Models\PersonalPaymentReceived::class, 'customer_id')
            ->where('invoice_type', 'sale');
    }

    public function returnInvoices()
    {
        return $this->hasMany(\App\Models\PersonalReturnInvoice::class, 'customer_id');
    }
}
