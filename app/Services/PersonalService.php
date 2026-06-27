<?php

namespace App\Services;

use App\DTOs\PersonalStockEntryDTO;
use App\DTOs\PersonalPaymentReceivedDTO;
use App\DTOs\PersonalReturnInvoiceDTO;
use App\DTOs\PersonalPaymentSentDTO;
use App\Models\PersonalStockEntry;
use App\Models\PersonalPaymentReceived;
use App\Models\PersonalReturnInvoice;
use App\Models\PersonalPaymentSent;
use App\Models\Bank;
use App\Models\BankLedger;
use App\Repositories\Contracts\PersonalStockRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentRepositoryInterface;
use App\Repositories\Contracts\PersonalReturnRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentSentRepositoryInterface;
use App\Events\PersonalPaymentRecorded;
use Illuminate\Support\Facades\DB;

class PersonalService
{
    public function __construct(
        protected readonly PersonalStockRepositoryInterface $stockRepo,
        protected readonly PersonalPaymentRepositoryInterface $paymentRepo,
        protected readonly PersonalReturnRepositoryInterface $returnRepo,
        protected readonly PersonalPaymentSentRepositoryInterface $paymentSentRepo
    ) {}

    /**
     * Store Purchased Stock Entry with sub-items inside a Transaction.
     */
    public function storeStockEntry(PersonalStockEntryDTO $dto): PersonalStockEntry
    {
        return DB::transaction(function () use ($dto) {
            $containerNo = $dto->containerNo ?: $this->generateNextStockInvoiceNo();
            $entry = $this->stockRepo->create(array_merge($dto->toArray(), [
                'container_no' => $containerNo
            ]));

            foreach ($dto->smallBaleItems as $item) {
                $entry->items()->create([
                    'bale_type' => \App\Enums\BaleType::SMALL->value,
                    'no_of_bales' => $item['no_of_bales'],
                    'item_name' => $item['item_name'],
                    'company' => $item['company'],
                    'weight' => $item['weight'],
                    'rate' => $item['rate'],
                ]);
            }

            foreach ($dto->bigBaleItems as $item) {
                $entry->items()->create([
                    'bale_type' => \App\Enums\BaleType::BIG->value,
                    'no_of_bales' => $item['no_of_bales'],
                    'item_name' => $item['item_name'],
                    'company' => $item['company'],
                    'weight' => $item['weight'],
                    'rate' => $item['rate'],
                ]);
            }

            event(new \App\Events\PersonalStockEntrySaved($entry));

            return $entry;
        });
    }

    /**
     * Update Purchased Stock Entry with sub-items inside a Transaction.
     */
    public function updateStockEntry(int $id, PersonalStockEntryDTO $dto): ?PersonalStockEntry
    {
        return DB::transaction(function () use ($id, $dto) {
            $entry = $this->stockRepo->update($id, $dto->toArray());
            if (!$entry) {
                return null;
            }

            // Delete old items and insert new ones
            $entry->items()->delete();

            foreach ($dto->smallBaleItems as $item) {
                $entry->items()->create([
                    'bale_type' => \App\Enums\BaleType::SMALL->value,
                    'no_of_bales' => $item['no_of_bales'],
                    'item_name' => $item['item_name'],
                    'company' => $item['company'],
                    'weight' => $item['weight'],
                    'rate' => $item['rate'],
                ]);
            }

            foreach ($dto->bigBaleItems as $item) {
                $entry->items()->create([
                    'bale_type' => \App\Enums\BaleType::BIG->value,
                    'no_of_bales' => $item['no_of_bales'],
                    'item_name' => $item['item_name'],
                    'company' => $item['company'],
                    'weight' => $item['weight'],
                    'rate' => $item['rate'],
                ]);
            }

            event(new \App\Events\PersonalStockEntrySaved($entry));

            return $entry;
        });
    }

    /**
     * Delete Purchased Stock Entry.
     */
    public function deleteStockEntry(int $id): bool
    {
        return $this->stockRepo->delete($id);
    }

    /**
     * Get auto-generated next Purchased Stock Invoice/Container Number.
     */
    public function generateNextStockInvoiceNo(): string
    {
        // 6 characters UUID-like alphanumeric key (e.g. 7D2AE8)
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    }

    /**
     * Get auto-generated next Invoice Number with SAL prefix (Sales).
     * Only scans rows that start with 'SAL-' so it never collides with PAR- entries.
     */
    public function generateNextInvoiceNo(): string
    {
        $latest = \App\Models\PersonalPaymentReceived::where('invoice_no', 'like', 'SAL-%')
            ->latest('id')
            ->first();
        if ($latest) {
            preg_match('/SAL-(\d+)/', $latest->invoice_no, $matches);
            $nextNum = isset($matches[1]) ? ((int) $matches[1]) + 1 : 10001;
        } else {
            $nextNum = 10001;
        }
        return 'SAL-' . $nextNum;
    }

    /**
     * Get auto-generated next Payment Received Invoice Number with PAR prefix.
     * Only scans rows that start with 'PAR-' so it never collides with SAL- entries.
     */
    public function generateNextPaymentReceivedInvoiceNo(): string
    {
        $latest = \App\Models\PersonalPaymentReceived::where('invoice_no', 'like', 'PAR-%')
            ->latest('id')
            ->first();
        if ($latest) {
            preg_match('/PAR-(\d+)/', $latest->invoice_no, $matches);
            $nextNum = isset($matches[1]) ? ((int) $matches[1]) + 1 : 10001;
        } else {
            $nextNum = 10001;
        }
        return 'PAR-' . $nextNum;
    }

    /**
     * Store Payment Received (Sales Invoice) with sub-cheques and online logs.
     */
    public function storePaymentReceived(PersonalPaymentReceivedDTO $dto): PersonalPaymentReceived
    {
        return DB::transaction(function () use ($dto) {
            // Auto generate next PAR- invoice (Payment Received, distinct from SAL- Sale Invoices)
            $invoiceNo = $this->generateNextPaymentReceivedInvoiceNo();

            $paymentData = array_merge($dto->toArray(), [
                'invoice_no' => $invoiceNo
            ]);

            $payment = $this->paymentRepo->create($paymentData);

            // Create ledger entries for cheques (credit to bank)
            foreach ($dto->cheques as $cheque) {
                $payment->cheques()->create([
                    'bank_name' => $cheque['bank_name'],
                    'check_no' => $cheque['check_no'],
                    'due_date' => $cheque['due_date'],
                    'to_name' => $cheque['to_name'],
                    'amount' => $cheque['amount'],
                ]);

                // Create bank ledger entry
                $this->createBankLedgerEntry(
                    $cheque['bank_name'],
                    'credit',
                    'cheque',
                    $cheque['amount'],
                    'Payment Received - Cheque',
                    $invoiceNo,
                    'payment_received',
                    $payment->id,
                    $dto->dateReceived
                );
            }

            // Create ledger entries for online payments (credit to bank)
            foreach ($dto->onlines as $online) {
                $payment->onlines()->create([
                    'bank_name' => $online['bank_name'],
                    'name' => $online['name'],
                    'payment_date' => $online['date'],
                    'from_name' => $online['from'] ?? $online['from_name'] ?? null,
                    'to_name' => $online['to'] ?? $online['to_name'] ?? null,
                    'amount' => $online['amount'],
                ]);

                // Create bank ledger entry
                $this->createBankLedgerEntry(
                    $online['bank_name'],
                    'credit',
                    'online',
                    $online['amount'],
                    'Payment Received - Online',
                    $invoiceNo,
                    'payment_received',
                    $payment->id,
                    $dto->dateReceived
                );
            }

            // Stock deduction logic for sale invoice items
            if (isset($dto->items) && is_array($dto->items)) {
                foreach ($dto->items as $item) {
                    $this->deductStockFromInventory($item);
                }
            }

            // Dispatch payment success event
            event(new PersonalPaymentRecorded($payment));

            return $payment;
        });
    }

    /**
     * Deduct stock from inventory based on sale invoice items.
     */
    protected function deductStockFromInventory(array $item): void
    {
        $itemName = $item['item_name'] ?? null;
        $smallBales = (int) ($item['small_bales'] ?? 0);
        $bigBales = (int) ($item['big_bales'] ?? 0);

        if (!$itemName) {
            return;
        }

        // Deduct from Small Bales inventory
        if ($smallBales > 0) {
            $smallBale = \App\Models\SmallBale::where('name', $itemName)
                ->where('category', 'small-bales')
                ->first();
            if (!$smallBale || (int) $smallBale->stock < $smallBales) {
                $stockCount = $smallBale ? $smallBale->stock : 0;
                throw new \Exception("Warning: Insufficient stock for Small Bales item '{$itemName}'. Current stock is: {$stockCount}");
            }
            $smallBale->update([
                'stock' => $smallBale->stock - $smallBales,
                'sale' => ($smallBale->sale ?? 0) + $smallBales
            ]);
        }

        // Deduct from Big Bales inventory (if exists)
        if ($bigBales > 0) {
            $bigBale = \App\Models\SmallBale::where('name', $itemName)
                ->where('category', 'big-bales')
                ->first();
            if (!$bigBale || (int) $bigBale->stock < $bigBales) {
                $stockCount = $bigBale ? $bigBale->stock : 0;
                throw new \Exception("Warning: Insufficient stock for Big Bales item '{$itemName}'. Current stock is: {$stockCount}");
            }
            $bigBale->update([
                'stock' => $bigBale->stock - $bigBales,
                'sale' => ($bigBale->sale ?? 0) + $bigBales
            ]);
        }
    }

    /**
     * Store Sell Return Invoice with sub-items inside a Transaction.
     */
    public function storeReturnInvoice(PersonalReturnInvoiceDTO $dto): PersonalReturnInvoice
    {
        return DB::transaction(function () use ($dto) {
            $returnInvoice = $this->returnRepo->create($dto->toArray());

            foreach ($dto->items as $item) {
                $returnInvoice->items()->create([
                    'item_name' => $item['item_name'],
                    'is_small_bales' => $item['small_bales'],
                    'is_big_bales' => $item['big_bales'],
                    'no_of_bales' => $item['no_of_bales'],
                    'amount' => $item['amount'],
                ]);

                // Wapas add to stock logic
                $itemName = $item['item_name'];
                $noOfBales = (int) $item['no_of_bales'];
                
                if ($item['small_bales']) {
                    $smallBale = \App\Models\SmallBale::where('name', $itemName)
                        ->where('category', 'small-bales')
                        ->first();
                    if ($smallBale) {
                        $smallBale->update([
                            'stock' => $smallBale->stock + $noOfBales,
                            'sale' => max(0, ($smallBale->sale ?? 0) - $noOfBales)
                        ]);
                    }
                } elseif ($item['big_bales']) {
                    $bigBale = \App\Models\SmallBale::where('name', $itemName)
                        ->where('category', 'big-bales')
                        ->first();
                    if ($bigBale) {
                        $bigBale->update([
                            'stock' => $bigBale->stock + $noOfBales,
                            'sale' => max(0, ($bigBale->sale ?? 0) - $noOfBales)
                        ]);
                    }
                }
            }

            return $returnInvoice;
        });
    }

    /**
     * Get auto-generated next Payment Sent Invoice Number with PAS prefix.
     */
    public function generateNextPaymentSentInvoiceNo(): string
    {
        $latest = $this->paymentSentRepo->getLatest();
        if ($latest) {
            preg_match('/PAS-(\d+)/', $latest->invoice_no, $matches);
            $nextNum = isset($matches[1]) ? ((int) $matches[1]) + 1 : 10001;
        } else {
            $nextNum = 10001;
        }
        return 'PAS-' . $nextNum;
    }

    /**
     * Store Payment Sent with sub-cheques and online logs.
     */
    public function storePaymentSent(PersonalPaymentSentDTO $dto): PersonalPaymentSent
    {
        return DB::transaction(function () use ($dto) {
            // Skip bank balance checks per client request (remove restrictions)

            $invoiceNo = $this->generateNextPaymentSentInvoiceNo();

            $paymentData = array_merge($dto->toArray(), [
                'invoice_no' => $invoiceNo
            ]);

            $payment = $this->paymentSentRepo->create($paymentData);

            // Create ledger entries for cheques (debit from bank)
            foreach ($dto->cheques as $cheque) {
                $payment->cheques()->create([
                    'bank_name' => $cheque['bank_name'],
                    'check_no' => $cheque['check_no'],
                    'due_date' => $cheque['due_date'],
                    'to_name' => $cheque['to_name'],
                    'amount' => $cheque['amount'],
                ]);

                // Create bank ledger entry
                $this->createBankLedgerEntry(
                    $cheque['bank_name'],
                    'debit',
                    'cheque',
                    $cheque['amount'],
                    'Payment Sent - Cheque',
                    $invoiceNo,
                    'payment_sent',
                    $payment->id,
                    $dto->dateSent
                );
            }

            // Create ledger entries for online payments (debit from bank)
            foreach ($dto->onlines as $online) {
                $payment->onlines()->create([
                    'bank_name' => $online['bank_name'],
                    'name' => $online['name'],
                    'payment_date' => $online['date'],
                    'from_name' => $online['from'] ?? $online['from_name'] ?? null,
                    'to_name' => $online['to'] ?? $online['to_name'] ?? null,
                    'amount' => $online['amount'],
                ]);

                // Create bank ledger entry
                $this->createBankLedgerEntry(
                    $online['bank_name'],
                    'debit',
                    'online',
                    $online['amount'],
                    'Payment Sent - Online',
                    $invoiceNo,
                    'payment_sent',
                    $payment->id,
                    $dto->dateSent
                );
            }

            return $payment;
        });
    }

    /**
     * Create a bank ledger entry with running balance calculation.
     */
    protected function createBankLedgerEntry(
        string $bankName,
        string $transactionType,
        string $paymentType,
        float $amount,
        string $description,
        ?string $invoiceNo,
        ?string $referenceType,
        ?int $referenceId,
        string $transactionDate
    ): void {
        // Find bank by name
        $bank = Bank::where('bank_name', $bankName)->first();
        if (!$bank) {
            return; // Skip if bank doesn't exist
        }

        // Get current balance from latest ledger entry or bank's opening balance
        $latestEntry = $bank->ledger()->latest()->first();
        $currentBalance = $latestEntry ? $latestEntry->balance_after : (float) $bank->opening_balance;

        // Calculate new balance
        $newBalance = $transactionType === 'credit' 
            ? $currentBalance + $amount 
            : $currentBalance - $amount;

        // Create ledger entry
        BankLedger::create([
            'bank_id' => $bank->id,
            'invoice_no' => $invoiceNo,
            'transaction_type' => $transactionType,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => $transactionDate,
        ]);

        // Sync current_balance back to Bank table
        $bank->update(['current_balance' => $newBalance]);
    }

    /**
     * Store Customer Sale Invoice (SAL) and deduct stock.
     */
    public function storeCustomerSaleInvoice(array $data): PersonalPaymentReceived
    {
        return DB::transaction(function () use ($data) {
            $customerId = $data['customerId'] ?? null;
            if ($customerId) {
                $customer = \App\Models\PersonalCustomer::find($customerId);
            } else {
                $customerName = $data['customerName'] ?? '';
                $customer = \App\Models\PersonalCustomer::where('name', $customerName)->first();
            }
            $customerId = $customer ? $customer->id : null;
            $customerName = $customer ? $customer->name : ($data['customerName'] ?? '');

            // Date formatting helper
            $date = date('Y-m-d');
            if (!empty($data['dateAdded'])) {
                $parsed = strtotime($data['dateAdded']);
                if ($parsed !== false) {
                    $date = date('Y-m-d', $parsed);
                }
            }

            // Create the PersonalPaymentReceived entry (which acts as the Sales Invoice SAL)
            $payment = PersonalPaymentReceived::create([
                'invoice_no' => $data['invoiceNo'],
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'to_name' => $data['supplierName'] ?? '',
                'date_received' => $date,
                'cash_amount' => 0,
                'total_amount' => $data['totalAmountPayable'] ?? 0,
                'paid_amount' => 0,
                'due_amount' => $data['totalAmountPayable'] ?? 0,
                'description' => $data['description'] ?? 'Sale Invoice',
            ]);

            // Save Small Bale Items
            if (!empty($data['smallBaleItems'])) {
                foreach ($data['smallBaleItems'] as $item) {
                    if (empty($item['itemName']) || empty($item['noOfBales'])) {
                        continue;
                    }
                    $noOfBales = (int) $item['noOfBales'];
                    $weight = (float) ($item['weight'] ?? 0);
                    $rate = (float) ($item['rate'] ?? 0);
                    
                    // Frontend formula:
                    $amount = $noOfBales * $rate * 0.05;

                    $payment->items()->create([
                        'bale_type' => 'small',
                        'item_name' => $item['itemName'],
                        'company' => $item['company'] ?? '',
                        'no_of_bales' => $noOfBales,
                        'weight' => $weight,
                        'rate' => $rate,
                        'amount' => $amount,
                    ]);

                    // Deduct stock
                    $this->deductStockFromInventory([
                        'item_name' => $item['itemName'],
                        'small_bales' => $noOfBales,
                        'big_bales' => 0,
                    ]);
                }
            }

            // Save Big Bale Items
            if (!empty($data['bigBaleItems'])) {
                foreach ($data['bigBaleItems'] as $item) {
                    if (empty($item['itemName']) || empty($item['noOfBales'])) {
                        continue;
                    }
                    $noOfBales = (int) $item['noOfBales'];
                    $weight = (float) ($item['weight'] ?? 0);
                    $rate = (float) ($item['rate'] ?? 0);
                    
                    // Frontend formula:
                    $amount = $noOfBales * $rate * 0.04;

                    $payment->items()->create([
                        'bale_type' => 'big',
                        'item_name' => $item['itemName'],
                        'no_of_bales' => $noOfBales,
                        'weight' => $weight,
                        'rate' => $rate,
                        'amount' => $amount,
                    ]);

                    // Deduct stock
                    $this->deductStockFromInventory([
                        'item_name' => $item['itemName'],
                        'small_bales' => 0,
                        'big_bales' => $noOfBales,
                    ]);
                }
            }

            return $payment;
        });
    }
}
