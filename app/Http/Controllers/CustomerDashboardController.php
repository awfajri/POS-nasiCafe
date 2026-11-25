<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Batalkan pesanan self-order yang kedaluwarsa (berjalan walau tanpa cron).
        app(TransactionService::class)->cancelExpired();

        $user = $request->user();

        // Semua data pelanggan di-query lewat relasi milik user yang login.
        $active = $user->transactions()
            ->with('details.menu')
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->get();

        $history = $user->transactions()
            ->with('details.menu')
            ->latest()
            ->paginate(10);

        $totalTransactions = $user->transactions()->active()->count();

        return view('customer.dashboard', [
            'active' => $active,
            'history' => $history,
            'totalTransactions' => $totalTransactions,
            // Hanya digit, supaya aman dipakai di URL wa.me.
            'waNumber' => preg_replace('/\D/', '', (string) config('nasicafe.cashier_whatsapp')),
        ]);
    }
}