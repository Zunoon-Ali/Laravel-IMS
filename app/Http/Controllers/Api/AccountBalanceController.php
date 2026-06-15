<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\PersonalPaymentCheque;
use App\Models\PersonalPaymentOnline;
use App\Models\PersonalPaymentReceived;
use App\Models\PersonalPaymentSent;
use App\Models\PersonalPaymentSentCheque;
use App\Models\PersonalPaymentSentOnline;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AccountBalanceController extends Controller
{
    use ApiResponse;

    /**
     * Get company overview for a given company slug.
     * Returns aggregate totals from payment records.
     */
    public function getOverview(string $companyId = 'long'): JsonResponse
    {
        try {
            $companies = [
                'long'     => ['companyName' => 'Long International',     'address' => '123 Business Street, Karachi Pakistan', 'phone' => '+92 354963467', 'email' => 'info@longinternational.com'],
                'pak'      => ['companyName' => 'Pak Trading Company',    'address' => '123 Business Street, Karachi Pakistan', 'phone' => '+92 354953467', 'email' => 'info@paktradingco.com'],
                'mountain' => ['companyName' => 'Mountain',               'address' => '456 Main Highway, Lahore Pakistan',     'phone' => '+92 312345678', 'email' => 'info@mountain.com'],
            ];

            $info = $companies[$companyId] ?? $companies['long'];

            $totalReceived = (float) PersonalPaymentReceived::sum('total_amount');
            $totalSent     = (float) PersonalPaymentSent::sum('total_amount');

            return $this->successResponse([
                'companyName'    => $info['companyName'],
                'address'        => $info['address'],
                'phone'          => $info['phone'],
                'email'          => $info['email'],
                'totalRemaining' => max(0, $totalReceived - $totalSent),
                'totalSent'      => $totalSent,
            ], 'Company overview retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getOverview failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve overview', 500);
        }
    }

    /**
     * Get aggregated totals: cheque count, total received, total sent, balance.
     */
    public function getTotals(): JsonResponse
    {
        try {
            $totalReceived  = (float) PersonalPaymentReceived::sum('total_amount');
            $totalSent      = (float) PersonalPaymentSent::sum('total_amount');
            $totalCheques   = PersonalPaymentCheque::count() + PersonalPaymentSentCheque::count();
            $totalBalance   = max(0, $totalReceived - $totalSent);

            return $this->successResponse([
                'totalCheques'  => $totalCheques,
                'totalReceived' => $totalReceived,
                'totalSent'     => $totalSent,
                'totalBalance'  => $totalBalance,
            ], 'Account totals retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getTotals failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve totals', 500);
        }
    }

    /**
     * Get merged list of payments received + sent for the main transaction table.
     */
    public function getPayments(): JsonResponse
    {
        try {
            $received = PersonalPaymentReceived::latest('id')->get()->map(function ($p) {
                return [
                    'id'              => $p->id,
                    'date'            => optional($p->date_received)->format('d-M-Y') ?? $p->date_received,
                    'invoiceNo'       => $p->invoice_no,
                    'customerName'    => $p->customer_name,
                    'paymentReceived' => (float) $p->total_amount,
                    'paymentSent'     => 0,
                    'totalRemaining'  => (float) $p->due_amount,
                    'type'            => 'received',
                ];
            });

            $sent = PersonalPaymentSent::latest('id')->get()->map(function ($p) {
                return [
                    'id'              => $p->id + 100000, // offset to avoid id collision
                    'date'            => optional($p->date_sent)->format('d-M-Y') ?? $p->date_sent,
                    'invoiceNo'       => $p->invoice_no,
                    'customerName'    => $p->customer_name,
                    'paymentReceived' => 0,
                    'paymentSent'     => (float) $p->total_amount,
                    'totalRemaining'  => (float) $p->due_amount,
                    'type'            => 'sent',
                ];
            });

            $all = $received->concat($sent)->sortByDesc('id')->values();

            return $this->successResponse($all, 'Payments list retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getPayments failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payments', 500);
        }
    }

    /**
     * Get all cheques (received + sent) combined.
     */
    public function getCheques(): JsonResponse
    {
        try {
            $received = PersonalPaymentCheque::with('paymentReceived')->latest('id')->get()->map(function ($c) {
                return [
                    'id'       => $c->id,
                    'date'     => optional($c->paymentReceived?->date_received)->format('d-M-Y') ?? now()->format('d-M-Y'),
                    'chequeNo' => $c->check_no,
                    'name'     => $c->paymentReceived?->customer_name ?? '—',
                    'type'     => 'Received',
                    'amount'   => (float) $c->amount,
                    'status'   => 'Cleared',
                    'remarks'  => 'Bank: ' . $c->bank_name . ' | Due: ' . $c->due_date,
                ];
            });

            $sent = PersonalPaymentSentCheque::with('paymentSent')->latest('id')->get()->map(function ($c) {
                return [
                    'id'       => $c->id + 100000,
                    'date'     => optional($c->paymentSent?->date_sent)->format('d-M-Y') ?? now()->format('d-M-Y'),
                    'chequeNo' => $c->check_no,
                    'name'     => $c->paymentSent?->customer_name ?? '—',
                    'type'     => 'Sent',
                    'amount'   => (float) $c->amount,
                    'status'   => 'Cleared',
                    'remarks'  => 'Bank: ' . $c->bank_name . ' | Due: ' . $c->due_date,
                ];
            });

            $all = $received->concat($sent)->values();

            return $this->successResponse($all, 'Cheques retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getCheques failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve cheques', 500);
        }
    }

    /**
     * Get all cash entries (received + sent) combined.
     */
    public function getCash(): JsonResponse
    {
        try {
            $received = PersonalPaymentReceived::where('cash_amount', '>', 0)->latest('id')->get()->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'date'           => optional($p->date_received)->format('d-M-Y') ?? $p->date_received,
                    'type'           => 'Received',
                    'description'    => 'Cash received from ' . $p->customer_name,
                    'receivedAmount' => (float) $p->cash_amount,
                    'sentAmount'     => 0,
                    'status'         => 'CASH',
                    'remarks'        => 'Invoice #' . $p->invoice_no,
                ];
            });

            $sent = PersonalPaymentSent::where('cash_amount', '>', 0)->latest('id')->get()->map(function ($p) {
                return [
                    'id'             => $p->id + 100000,
                    'date'           => optional($p->date_sent)->format('d-M-Y') ?? $p->date_sent,
                    'type'           => 'Sent',
                    'description'    => 'Cash sent to ' . $p->customer_name,
                    'receivedAmount' => 0,
                    'sentAmount'     => (float) $p->cash_amount,
                    'status'         => 'CASH',
                    'remarks'        => 'Invoice #' . $p->invoice_no,
                ];
            });

            $all = $received->concat($sent)->values();

            return $this->successResponse($all, 'Cash entries retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getCash failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve cash entries', 500);
        }
    }

    /**
     * Get bank cards with computed balance from online transactions.
     */
    public function getBankCards(): JsonResponse
    {
        try {
            $banks = Bank::all()->map(function ($bank) {
                // Compute balance: sum of online received - sum of online sent for this bank
                $received = (float) PersonalPaymentOnline::where('bank_name', $bank->bank_name)->sum('amount');
                $sent     = (float) PersonalPaymentSentOnline::where('bank_name', $bank->bank_name)->sum('amount');
                $balance  = max(0, $received - $sent);

                return [
                    'id'       => (string) $bank->id,
                    'bankName' => $bank->bank_name,
                    'balance'  => $balance,
                    'logoType' => strtolower(str_contains(strtolower($bank->bank_name), 'meezan') ? 'meezan'
                        : (str_contains(strtolower($bank->bank_name), 'alfalah') ? 'alfalah' : 'bahl')),
                ];
            });

            return $this->successResponse($banks, 'Bank cards retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getBankCards failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve bank cards', 500);
        }
    }

    /**
     * Get all bank transactions from online payments (received + sent).
     */
    public function getBankTransactions(): JsonResponse
    {
        try {
            $received = PersonalPaymentOnline::with('paymentReceived')->latest('id')->get()->map(function ($o) {
                return [
                    'id'             => $o->id,
                    'date'           => $o->payment_date ?? optional($o->paymentReceived?->date_received)->format('d-M-Y') ?? now()->format('d-M-Y'),
                    'name'           => $o->paymentReceived?->customer_name ?? $o->from_name ?? '—',
                    'invoiceNo'      => $o->paymentReceived?->invoice_no ?? '—',
                    'type'           => 'Online',
                    'description'    => 'Online payment received | Bank: ' . $o->bank_name,
                    'debit'          => (float) $o->amount,
                    'credit'         => 0,
                    'status'         => 'Received',
                    'totalBalance'   => (float) $o->amount,
                    'openingBalance' => 0,
                ];
            });

            $sent = PersonalPaymentSentOnline::with('paymentSent')->latest('id')->get()->map(function ($o) {
                return [
                    'id'             => $o->id + 100000,
                    'date'           => optional($o->payment_date)->format('d-M-Y') ?? optional($o->paymentSent?->date_sent)->format('d-M-Y') ?? now()->format('d-M-Y'),
                    'name'           => $o->paymentSent?->customer_name ?? $o->from_name ?? '—',
                    'invoiceNo'      => $o->paymentSent?->invoice_no ?? '—',
                    'type'           => 'Online',
                    'description'    => 'Online payment sent | Bank: ' . $o->bank_name,
                    'debit'          => 0,
                    'credit'         => (float) $o->amount,
                    'status'         => 'Sent',
                    'totalBalance'   => (float) $o->amount,
                    'openingBalance' => 0,
                ];
            });

            $all = $received->concat($sent)->values();

            return $this->successResponse($all, 'Bank transactions retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getBankTransactions failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve bank transactions', 500);
        }
    }

    /**
     * Get detailed payment info by invoice number (from received or sent).
     */
    public function getDetailedPayment(string $invoiceNo): JsonResponse
    {
        try {
            // Try received first
            $payment = PersonalPaymentReceived::with(['cheques', 'onlines'])
                ->where('invoice_no', $invoiceNo)->first();

            if ($payment) {
                $cheque = $payment->cheques->first();
                $online = $payment->onlines->first();

                return $this->successResponse([
                    'invoiceNo'         => $payment->invoice_no,
                    'date'              => optional($payment->date_received)->format('d-M-Y') ?? $payment->date_received,
                    'customerName'      => $payment->customer_name,
                    'description'       => $payment->description ?? 'Payment Received',
                    'cashPaymentsAmount'=> (float) $payment->cash_amount,
                    'totalAmount'       => (float) $payment->total_amount,
                    'chequePayments'    => $cheque ? [
                        'chequeNo'     => $cheque->check_no,
                        'accountTitle' => $cheque->to_name,
                        'bankName'     => $cheque->bank_name,
                        'dueDate'      => $cheque->due_date,
                        'amount'       => (float) $cheque->amount,
                    ] : null,
                    'onlinePayments'    => $online ? [
                        'transactionId' => $online->name ?? '—',
                        'from'          => $online->from_name ?? '—',
                        'bankName'      => $online->bank_name,
                        'to'            => $online->to_name ?? '—',
                        'amount'        => (float) $online->amount,
                    ] : null,
                ], 'Detailed payment retrieved');
            }

            // Try sent
            $sent = PersonalPaymentSent::with(['cheques', 'onlines'])
                ->where('invoice_no', $invoiceNo)->first();

            if ($sent) {
                $cheque = $sent->cheques->first();
                $online = $sent->onlines->first();

                return $this->successResponse([
                    'invoiceNo'         => $sent->invoice_no,
                    'date'              => optional($sent->date_sent)->format('d-M-Y') ?? $sent->date_sent,
                    'customerName'      => $sent->customer_name,
                    'description'       => $sent->description ?? 'Payment Sent',
                    'cashPaymentsAmount'=> (float) $sent->cash_amount,
                    'totalAmount'       => (float) $sent->total_amount,
                    'chequePayments'    => $cheque ? [
                        'chequeNo'     => $cheque->check_no,
                        'accountTitle' => $cheque->to_name,
                        'bankName'     => $cheque->bank_name,
                        'dueDate'      => $cheque->due_date,
                        'amount'       => (float) $cheque->amount,
                    ] : null,
                    'onlinePayments'    => $online ? [
                        'transactionId' => $online->name ?? '—',
                        'from'          => $online->from_name ?? '—',
                        'bankName'      => $online->bank_name,
                        'to'            => $online->to_name ?? '—',
                        'amount'        => (float) $online->amount,
                    ] : null,
                ], 'Detailed payment sent retrieved');
            }

            return $this->errorResponse('Invoice not found', 404);
        } catch (\Exception $e) {
            Log::error('AccountBalance getDetailedPayment failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payment detail', 500);
        }
    }
}
