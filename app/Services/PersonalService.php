<?php

namespace App\Services;

use App\DTOs\PersonalStockEntryDTO;
use App\DTOs\PersonalPaymentReceivedDTO;
use App\DTOs\PersonalReturnInvoiceDTO;
use App\Models\PersonalStockEntry;
use App\Models\PersonalPaymentReceived;
use App\Models\PersonalReturnInvoice;
use App\Repositories\Contracts\PersonalStockRepositoryInterface;
use App\Repositories\Contracts\PersonalPaymentRepositoryInterface;
use App\Repositories\Contracts\PersonalReturnRepositoryInterface;
use App\Events\PersonalPaymentRecorded;
use Illuminate\Support\Facades\DB;

class PersonalService
{
    public function __construct(
        protected readonly PersonalStockRepositoryInterface $stockRepo,
        protected readonly PersonalPaymentRepositoryInterface $paymentRepo,
        protected readonly PersonalReturnRepositoryInterface $returnRepo
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

            return $entry;
        });
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
     * Get auto-generated next Invoice Number.
     */
    public function generateNextInvoiceNo(): string
    {
        $latest = $this->paymentRepo->getLatest();
        if ($latest) {
            preg_match('/INV-(\d+)/', $latest->invoice_no, $matches);
            $nextNum = isset($matches[1]) ? ((int) $matches[1]) + 1 : 10001;
        } else {
            $nextNum = 10001;
        }
        return 'INV-' . $nextNum;
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

            foreach ($dto->cheques as $cheque) {
                $payment->cheques()->create([
                    'bank_name' => $cheque['bank_name'],
                    'check_no' => $cheque['check_no'],
                    'due_date' => $cheque['due_date'],
                    'to_name' => $cheque['to_name'],
                    'amount' => $cheque['amount'],
                ]);
            }

            foreach ($dto->onlines as $online) {
                $payment->onlines()->create([
                    'bank_name' => $online['bank_name'],
                    'name' => $online['name'],
                    'payment_date' => $online['date'],
                    'from_name' => $online['from'],
                    'to_name' => $online['to'],
                    'amount' => $online['amount'],
                ]);
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
}
