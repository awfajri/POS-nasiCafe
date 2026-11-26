<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosTransactionRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Transaction::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Transaction::PAYMENT_STATUSES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $transactions = Transaction::query()
            ->with('cashier')
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['payment_status'] ?? null, fn ($query, $value) => $query->where('payment_status', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }
    public function show(Transaction $transaction): View
    {
        $transaction->load(['details.menu', 'user', 'cashier', 'payments.cashier']);

        return view('transactions.show', [
            'transaction' => $transaction,
            'transitions' => $this->transactionService->allowedTransitions($transaction),
        ]);
    }

    public function storeFromPos(StorePosTransactionRequest $request): RedirectResponse
    {
        $transaction = $this->transactionService->createOrder(
            $request->validated('items'),
            $request->validated('customer_name'),
            $request->validated('table_number'),
            Transaction::SOURCE_POS,
            null,
            $request->user(),
        );

        if ($request->boolean('redirect_to_payment') && Route::has('payments.checkout')) {
            return redirect()->route('payments.checkout', $transaction)
                ->with('success', 'Pesanan berhasil dibuat.');
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Pesanan berhasil dibuat.');
    }

    public function updateStatus(UpdateStatusRequest $request, Transaction $transaction): RedirectResponse
    {
        try {
            $this->transactionService->changeStatus(
                $transaction,
                $request->string('status')->toString(),
                $request->user(),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success', 'Status transaksi berhasil diperbarui.');
    }
}