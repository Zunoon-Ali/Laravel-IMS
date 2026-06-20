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
use Illuminate\Http\Request;
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
            
            // Total balance is sum of all bank current balances
            $totalBalance   = (float) Bank::where('status', 'Active')->sum('current_balance');

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
     * Get drill-down ledger for a specific bank with pinned opening balance and filtering.
     */
    public function getBankLedger(Request $request, $bankId): JsonResponse
    {
        try {
            $bank = Bank::find($bankId);
            if (!$bank) {
                return $this->errorResponse('Bank not found', 404);
            }

            $query = \App\Models\BankLedger::where('bank_id', $bankId);

            // Apply filters
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('transaction_date', '>=', $request->from_date);
            }
            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('transaction_date', '<=', $request->to_date);
            }
            if ($request->has('type') && $request->type && $request->type !== 'All') {
                $type = $request->type === 'PAR' ? 'credit' : 'debit';
                $query->where('transaction_type', $type);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortOrder = $request->get('sort_order', 'desc'); // newest first by default
            $query->orderBy('transaction_date', $sortOrder)
                  ->orderBy('id', $sortOrder);

            // Pagination
            $perPage = (int) $request->get('per_page', 25);
            $ledgers = $query->paginate($perPage);

            // Map rows
            $formattedRows = collect($ledgers->items())->map(function ($row) {
                return [
                    'id' => $row->id,
                    'date' => optional($row->transaction_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'invoiceNo' => $row->invoice_no ?? '—',
                    'transactionType' => $row->transaction_type === 'credit' ? 'PAR' : 'PAS',
                    'description' => $row->description ?? '',
                    'debit' => $row->transaction_type === 'debit' ? (float) $row->amount : 0,
                    'credit' => $row->transaction_type === 'credit' ? (float) $row->amount : 0,
                    'runningBalance' => (float) $row->balance_after,
                ];
            });

            // Prepend Opening Balance Row on Page 1
            $page = (int) $ledgers->currentPage();
            if ($page === 1) {
                $openingRow = [
                    'id' => 'opening-' . $bank->id,
                    'date' => $bank->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'invoiceNo' => '—',
                    'transactionType' => 'Opening',
                    'description' => 'Opening Balance',
                    'debit' => 0,
                    'credit' => 0,
                    'runningBalance' => (float) $bank->opening_balance,
                    'isOpening' => true
                ];

                if ($sortOrder === 'asc') {
                    $formattedRows->prepend($openingRow);
                } else {
                    $formattedRows->push($openingRow);
                }
            }

            return $this->successResponse([
                'bank' => [
                    'id' => $bank->id,
                    'bankName' => $bank->bank_name,
                    'accountNumber' => $bank->account_number,
                    'branch' => $bank->branch,
                    'openingBalance' => (float) $bank->opening_balance,
                    'currentBalance' => (float) $bank->current_balance,
                    'status' => $bank->status,
                    'hasMismatch' => $bank->has_balance_mismatch,
                ],
                'data' => $formattedRows,
                'meta' => [
                    'current_page' => $ledgers->currentPage(),
                    'last_page' => $ledgers->lastPage(),
                    'per_page' => $ledgers->perPage(),
                    'total' => $ledgers->total(),
                ]
            ], 'Bank ledger retrieved successfully');

        } catch (\Exception $e) {
            Log::error('getBankLedger failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve bank ledger: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get filtered and paginated list of all payments (received + sent) combined.
     */
    public function getPayments(Request $request): JsonResponse
    {
        try {
            $from = $request->get('from_date');
            $to = $request->get('to_date');
            $type = $request->get('type', 'All'); // 'All', 'PAR', 'PAS'
            $paymentMode = $request->get('payment_mode', 'All');
            $minAmount = $request->get('min_amount');
            $maxAmount = $request->get('max_amount');
            $search = $request->get('search');
            $bankIds = $request->get('bank_ids');

            // Resolve bank names from IDs
            $bankNames = [];
            if ($bankIds) {
                // If it is a string comma separated, split it
                $idsArray = is_array($bankIds) ? $bankIds : explode(',', $bankIds);
                $bankNames = Bank::whereIn('id', $idsArray)->pluck('bank_name')->toArray();
            }

            $receivedQuery = PersonalPaymentReceived::with(['cheques', 'onlines']);
            $sentQuery = PersonalPaymentSent::with(['cheques', 'onlines']);

            // Date Filters
            if ($from) {
                $receivedQuery->whereDate('date_received', '>=', $from);
                $sentQuery->whereDate('date_sent', '>=', $from);
            }
            if ($to) {
                $receivedQuery->whereDate('date_received', '<=', $to);
                $sentQuery->whereDate('date_sent', '<=', $to);
            }

            // Amount Filters
            if ($minAmount) {
                $receivedQuery->where('total_amount', '>=', $minAmount);
                $sentQuery->where('total_amount', '>=', $minAmount);
            }
            if ($maxAmount) {
                $receivedQuery->where('total_amount', '<=', $maxAmount);
                $sentQuery->where('total_amount', '<=', $maxAmount);
            }

            // Search
            if ($search) {
                $receivedQuery->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhereHas('cheques', function ($cq) use ($search) {
                          $cq->where('bank_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('onlines', function ($oq) use ($search) {
                          $oq->where('bank_name', 'like', "%{$search}%");
                      });
                });

                $sentQuery->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhereHas('cheques', function ($cq) use ($search) {
                          $cq->where('bank_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('onlines', function ($oq) use ($search) {
                          $oq->where('bank_name', 'like', "%{$search}%");
                      });
                });
            }

            // Bank Name Filters
            if (!empty($bankNames)) {
                $receivedQuery->where(function ($q) use ($bankNames) {
                    $q->whereHas('cheques', function ($cq) use ($bankNames) {
                        $cq->whereIn('bank_name', $bankNames);
                    })->orWhereHas('onlines', function ($oq) use ($bankNames) {
                        $oq->whereIn('bank_name', $bankNames);
                    });
                });

                $sentQuery->where(function ($q) use ($bankNames) {
                    $q->whereHas('cheques', function ($cq) use ($bankNames) {
                        $cq->whereIn('bank_name', $bankNames);
                    })->orWhereHas('onlines', function ($oq) use ($bankNames) {
                        $oq->whereIn('bank_name', $bankNames);
                    });
                });
            }

            // Payment Mode Filters
            if ($paymentMode && $paymentMode !== 'All') {
                if ($paymentMode === 'Cash') {
                    $receivedQuery->where('cash_amount', '>', 0);
                    $sentQuery->where('cash_amount', '>', 0);
                } elseif ($paymentMode === 'Cheque') {
                    $receivedQuery->has('cheques');
                    $sentQuery->has('cheques');
                } elseif ($paymentMode === 'Online Transfer' || $paymentMode === 'Online') {
                    $receivedQuery->has('onlines');
                    $sentQuery->has('onlines');
                }
            }

            $received = collect();
            $sent = collect();

            if ($type === 'All' || $type === 'PAR') {
                $received = $receivedQuery->get()->map(function ($p) {
                    $banks = collect();
                    if ($p->cash_amount > 0) $banks->push('Cash');
                    $p->cheques->each(fn($c) => $banks->push($c->bank_name));
                    $p->onlines->each(fn($o) => $banks->push($o->bank_name));
                    $bankNameStr = $banks->unique()->filter()->implode(', ') ?: '—';

                    $mode = 'Cash';
                    $hasCash = $p->cash_amount > 0;
                    $hasCheque = $p->cheques->count() > 0;
                    $hasOnline = $p->onlines->count() > 0;
                    if ($hasCash && $hasCheque) $mode = 'Cash - Cheque';
                    elseif ($hasCash && $hasOnline) $mode = 'Cash - Online';
                    elseif ($hasCheque) $mode = 'Cheque';
                    elseif ($hasOnline) $mode = 'Online';

                    return [
                        'id' => $p->id,
                        'date' => $p->date_received ? $p->date_received->format('Y-m-d') : null,
                        'invoiceNo' => $p->invoice_no,
                        'bankName' => $bankNameStr,
                        'transactionType' => 'PAR',
                        'customerName' => $p->customer_name,
                        'paymentMode' => $mode,
                        'debit' => 0,
                        'credit' => (float) $p->total_amount,
                    ];
                });
            }

            if ($type === 'All' || $type === 'PAS') {
                $sent = $sentQuery->get()->map(function ($p) {
                    $banks = collect();
                    if ($p->cash_amount > 0) $banks->push('Cash');
                    $p->cheques->each(fn($c) => $banks->push($c->bank_name));
                    $p->onlines->each(fn($o) => $banks->push($o->bank_name));
                    $bankNameStr = $banks->unique()->filter()->implode(', ') ?: '—';

                    $mode = 'Cash';
                    $hasCash = $p->cash_amount > 0;
                    $hasCheque = $p->cheques->count() > 0;
                    $hasOnline = $p->onlines->count() > 0;
                    if ($hasCash && $hasCheque) $mode = 'Cash - Cheque';
                    elseif ($hasCash && $hasOnline) $mode = 'Cash - Online';
                    elseif ($hasCheque) $mode = 'Cheque';
                    elseif ($hasOnline) $mode = 'Online';

                    return [
                        'id' => $p->id + 100000,
                        'date' => $p->date_sent ? $p->date_sent->format('Y-m-d') : null,
                        'invoiceNo' => $p->invoice_no,
                        'bankName' => $bankNameStr,
                        'transactionType' => 'PAS',
                        'customerName' => $p->customer_name,
                        'paymentMode' => $mode,
                        'debit' => (float) $p->total_amount,
                        'credit' => 0,
                    ];
                });
            }

            $all = $received->concat($sent);

            // Sorting
            $sortBy = $request->get('sort_by', 'date');
            $sortOrder = $request->get('sort_order', 'desc');

            $all = $all->sortBy(function ($item) use ($sortBy) {
                switch ($sortBy) {
                    case 'amount':
                        return max($item['debit'], $item['credit']);
                    case 'bank_name':
                        return strtolower($item['bankName']);
                    case 'transaction_type':
                        return $item['transactionType'];
                    case 'date':
                    default:
                        return $item['date'];
                }
            }, SORT_REGULAR, $sortOrder === 'desc')->values();

            // Pagination
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 25);
            $total = $all->count();
            $paginatedItems = $all->slice(($page - 1) * $perPage, $perPage)->values();

            return $this->successResponse([
                'data' => $paginatedItems,
                'meta' => [
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                    'per_page' => $perPage,
                    'total' => $total,
                ]
            ], 'Payments list retrieved successfully');

        } catch (\Exception $e) {
            Log::error('getPayments failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payments: ' . $e->getMessage(), 500);
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
     * Get bank cards with computed balance from ledger.
     */
    public function getBankCards(): JsonResponse
    {
        try {
            // Fetch all banks (even Inactive ones, so they appear in consolidation totals, or only Active?)
            // Section 3.3: "One card per active bank showing: Bank Name + Current Balance"
            // So we fetch active banks.
            $banks = Bank::where('status', 'Active')->get()->map(function ($bank) {
                return [
                    'id'       => (string) $bank->id,
                    'bankName' => $bank->bank_name,
                    'balance'  => (float) $bank->current_balance,
                    'status'   => $bank->status,
                    'hasMismatch' => $bank->has_balance_mismatch,
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
     * Get all bank transactions from ledger.
     */
    public function getBankTransactions(): JsonResponse
    {
        try {
            $transactions = \App\Models\BankLedger::with('bank')
                ->latest('id')
                ->get()
                ->map(function ($ledger) {
                    return [
                        'id'             => $ledger->id,
                        'date'           => optional($ledger->transaction_date)->format('d-M-Y') ?? now()->format('d-M-Y'),
                        'name'           => $ledger->bank?->bank_name ?? '—',
                        'invoiceNo'      => $ledger->invoice_no ?? '—',
                        'type'           => ucfirst($ledger->payment_type),
                        'description'    => $ledger->description ?? ucfirst($ledger->transaction_type) . ' transaction',
                        'debit'          => $ledger->transaction_type === 'debit' ? (float) $ledger->amount : 0,
                        'credit'         => $ledger->transaction_type === 'credit' ? (float) $ledger->amount : 0,
                        'status'         => ucfirst($ledger->transaction_type),
                        'totalBalance'   => (float) $ledger->balance_after,
                        'openingBalance' => (float) $ledger->balance_after - ($ledger->transaction_type === 'credit' ? $ledger->amount : -$ledger->amount),
                    ];
                });

            return $this->successResponse($transactions, 'Bank transactions retrieved');
        } catch (\Exception $e) {
            Log::error('AccountBalance getBankTransactions failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve bank transactions', 500);
        }
    }

    /**
     * Get detailed payment info by invoice number.
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
