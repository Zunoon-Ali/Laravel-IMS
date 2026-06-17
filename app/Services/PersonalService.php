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
     */
    public function generateNextInvoiceNo(): string
    {
        $latest = $this->paymentRepo->getLatest();
        if ($latest) {
            preg_match('/SAL-(\d+)/', $latest->invoice_no, $matches);
            $nextNum = isset($matches[1]) ? ((int) $matches[1]) + 1 : 10001;
        } else {
            $nextNum = 10001;
        }
        return 'SAL-' . $nextNum;
    }

    /**
     * Store Payment Received (Sales Invoice) with sub-cheques and online logs.
     */
    public function storePaymentReceived(PersonalPaymentReceivedDTO $dto): PersonalPaymentReceived
    {
        return DB::transaction(function () use ($dto) {
            // Auto generate next invoice
            $invoiceNo = $this->generateNextInvoiceNo();

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
                    'from_name' => $online['from'],
                    'to_name' => $online['to'],
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

            // Dispatch payment success event
            event(new PersonalPaymentRecorded($payment));

            return $payment;
        });
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
                    'from_name' => $online['from'],
                    'to_name' => $online['to'],
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
        $currentBalance = $latestEntry ? $latestEntry->balance_after : (float) $bank->balance;

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
    }
}
