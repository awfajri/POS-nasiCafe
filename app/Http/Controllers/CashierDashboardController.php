<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transaction;
use Illuminate\View\View;

class CashierDashboardController extends Controller
{
    public function index(): View
    {
        return view('cashier.dashboard', [
            'pendingCount' => Transaction::query()
                ->where('status', Transaction::STATUS_PENDING)
                ->count(),
            'processingCount' => Transaction::query()
                ->where('status', Transaction::STATUS_PROCESSING)
                ->count(),
            'completedToday' => Transaction::today()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->count(),
            'totalToday' => Transaction::today()
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('total_amount'),
            'activeQueue' => Transaction::active()
                ->with(['details.menu', 'user', 'cashier'])
                ->oldest()
                ->limit(10)
                ->get(),
            'lowStockMenus' => Menu::query()
                ->available()
                ->lowStock()
                ->orderBy('stock')
                ->get(),
        ]);
    }
}