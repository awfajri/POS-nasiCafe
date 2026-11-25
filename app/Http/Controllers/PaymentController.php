<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function checkout(Transaction $transaction): View|RedirectResponse
    {
        if (! $transaction->canBePaid()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'Transaksi ini sudah lunas atau tidak dapat dibayar.');
        }

        $transaction->load(['details.menu', 'payments.cashier', 'user', 'cashier']);
        $remainingItems = $this->paymentService->remainingItems($transaction);
        $payments = $transaction->payments;

        return view('payments.checkout', compact('transaction', 'remainingItems', 'payments'));
    }

    public function payFull(ProcessPaymentRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();
        $method = $validated['payment_method'];
        $cashReceived = $method === 'cash' ? (int) $validated['cash_received'] : null;
        $payerName = $validated['payer_name'] ?? null;

        $payment = $this->paymentService->payRemaining(
            $transaction,
            $method,
            $request->user(),
            $cashReceived,
            $payerName
        );

        return redirect()
            ->route('payments.receipt', $payment)
            ->with('success', 'Pembayaran penuh berhasil dicatat.');
    }

    public function paySplit(ProcessPaymentRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();
        $method = $validated['payment_method'];
        $cashReceived = $method === 'cash' ? (int) $validated['cash_received'] : null;
        $payerName = $validated['payer_name'] ?? null;
        $items = $validated['items'];

        $payment = $this->paymentService->pay(
            $transaction,
            $items,
            $method,
            $request->user(),
            $cashReceived,
            $payerName
        );

        $hasRemaining = $this->paymentService->remainingItems($transaction->fresh())->isNotEmpty();

        if ($hasRemaining) {
            return redirect()
                ->route('payments.checkout', $transaction)
                ->with('success', 'Pembayaran split bill berhasil dicatat. Silakan lanjutkan sisa pembayaran.');
        }

        return redirect()
            ->route('payments.receipt', $payment)
            ->with('success', 'Seluruh tagihan berhasil dilunasi.');
    }

    public function confirmQris(Request $request, Transaction $transaction): RedirectResponse
    {
        if (
            $transaction->source !== 'self_order'
            || $transaction->status !== Transaction::STATUS_PENDING
            || $transaction->payment_status !== Transaction::PAYMENT_UNPAID
        ) {
            throw ValidationException::withMessages([
                'transaction' => 'Konfirmasi QRIS hanya berlaku untuk pesanan self-order berstatus pending dan belum dibayar.',
            ]);
        }

        $this->paymentService->payRemaining(
            $transaction,
            'qris',
            $request->user(),
            null,
            $transaction->customer_name
        );

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Pembayaran QRIS self-order berhasil dikonfirmasi dan pesanan diproses.');
    }

    public function receipt(TransactionPayment $payment): View
    {
        $payment->load(['transaction.details.menu', 'cashier']);

        $detailsById = $payment->transaction?->details?->keyBy('id') ?? collect();

        $paidItems = collect($payment->paid_items_json ?? [])->map(function (array $item) use ($detailsById): array {
            $detail = isset($item['detail_id']) ? $detailsById->get((int) $item['detail_id']) : null;

            return [
                'detail_id' => $item['detail_id'] ?? null,
                'menu_name' => $item['menu_name'] ?? $detail?->menu?->name ?? 'Menu',
                'qty' => (int) ($item['qty'] ?? 0),
                'subtotal' => (int) ($item['subtotal'] ?? 0),
            ];
        });

        return view('payments.receipt', compact('payment', 'paidItems'));
    }
}