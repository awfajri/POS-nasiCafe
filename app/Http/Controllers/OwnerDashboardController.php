<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Dashboard Owner: ringkasan omzet, transaksi, menu kritis, dan grafik.
     */
    public function index(): View
    {
        $today = now()->startOfDay();
        $todayRevenue = Transaction::query()
            ->where('status', '!=', Transaction::STATUS_CANCELLED)
            ->whereBetween('created_at', [$today, now()])
            ->sum('paid_amount');

        $monthStart = now()->startOfMonth();
        $monthRevenue = Transaction::query()
            ->where('status', '!=', Transaction::STATUS_CANCELLED)
            ->whereBetween('created_at', [$monthStart, now()])
            ->sum('paid_amount');

        $todayTransactions = Transaction::query()
            ->whereBetween('created_at', [$today, now()])
            ->count();

        // Menu kritis: stok <= min_stock, urut paling kritis (sisa stok naik).
        $criticalMenus = Menu::query()
            ->lowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $criticalCount = Menu::query()->lowStock()->count();

        return view('owner.dashboard', [
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,
            'todayTransactions' => $todayTransactions,
            'criticalCount' => $criticalCount,
            'criticalMenus' => $criticalMenus,
            'chartDaily' => $this->reportService->dailySales(14),
            'chartMonthly' => $this->reportService->monthlySales(6),
        ]);
    }
}
