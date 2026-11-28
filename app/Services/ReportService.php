<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Bulan singkat berbahasa Indonesia (independen dari locale aplikasi).
     */
    private const MONTHS_ID = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    /**
     * Ringkasan laporan pada rentang tanggal.
     *
     * Definisi omzet: penjumlahan `paid_amount` transaksi yang tidak
     * dibatalkan. Omzet per metode dihitung dari relasi payments.
     *
     * @return array{
     *     total_transactions: int,
     *     omzet: int,
     *     status_counts: array<string, int>,
     *     cash: int,
     *     qris: int,
     * }
     */
    public function summary(Carbon $start, Carbon $end): array
    {
        $query = Transaction::query()
            ->whereBetween('created_at', [$start, $end]);

        $totalTransactions = (clone $query)->count();

        $omzet = (clone $query)
            ->where('status', '!=', Transaction::STATUS_CANCELLED)
            ->sum('paid_amount');

        $statusCounts = (clone $query)
            ->get(['status'])
            ->groupBy('status')
            ->map(fn (Collection $group): int => $group->count())
            ->all();

        // Omzet per metode: jumlahkan amount_paid dari pembayaran pada
        // rentang, dikelompokkan payment_method.
        $methodTotals = $query
            ->with('payments')
            ->get()
            ->flatMap(fn (Transaction $tx): Collection => $tx->payments)
            ->groupBy('payment_method')
            ->map(fn (Collection $group): int => (int) $group->sum('amount_paid'));

        return [
            'total_transactions' => $totalTransactions,
            'omzet' => (int) $omzet,
            'status_counts' => $statusCounts,
            'cash' => (int) ($methodTotals['cash'] ?? 0),
            'qris' => (int) ($methodTotals['qris'] ?? 0),
        ];
    }

    /**
     * Query baris transaksi pada rentang tanggal, lengkap dengan kasir.
     */
    public function rowsQuery(Carbon $start, Carbon $end): Builder
    {
        return Transaction::query()
            ->with('cashier')
            ->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Omzet harian selama $days hari terakhir (termasuk hari ini).
     *
     * Definisi omzet: uang yang benar-benar diterima (penjumlahan
     * `paid_amount`) pada transaksi yang tidak dibatalkan. Transaksi
     * pending/cancelled selalu berpaid_amount 0, sehingga hanya transaksi
     * processing & completed yang berkontribusi.
     *
     * Pengelompokan dilakukan di PHP (collection->groupBy) agar aman
     * di SQLite (tidak memakai fungsi tanggal khusus MySQL).
     *
     * @return array<int, array{label: string, omzet: int}>
     */
    public function dailySales(int $days): array
    {
        $days = max(1, $days);

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        $grouped = $this->salesByDay($start, $end);

        $points = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            $points[] = [
                'label' => $date->format('d').' '.self::MONTHS_ID[(int) $date->format('n')],
                'omzet' => (int) ($grouped[$key] ?? 0),
            ];
        }

        return $points;
    }

    /**
     * Omzet bulanan selama $months bulan terakhir (termasuk bulan ini).
     *
     * @return array<int, array{label: string, omzet: int}>
     */
    public function monthlySales(int $months): array
    {
        $months = max(1, $months);

        $start = now()->startOfMonth()->subMonths($months - 1);
        $end = now()->endOfDay();

        // salesByDay mengembalikan kunci per hari (Y-m-d); kelompokkan ulang
        // ke per bulan (Y-m) agar sum omzet bulanan akurat.
        $daily = $this->salesByDay($start, $end);

        $monthly = [];

        foreach ($daily as $dayKey => $omzet) {
            $monthKey = substr($dayKey, 0, 7);
            $monthly[$monthKey] = ($monthly[$monthKey] ?? 0) + $omzet;
        }

        $points = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonthsNoOverflow($i);
            $key = $month->format('Y-m');

            $points[] = [
                'label' => self::MONTHS_ID[(int) $month->format('n')].' '.$month->format('Y'),
                'omzet' => (int) ($monthly[$key] ?? 0),
            ];
        }

        return $points;
    }

    /**
     * Ambil transaksi pada rentang lalu kelompokkan omzet per hari.
     *
     * @return array<string, int>  map 'Y-m-d' => jumlah paid_amount
     */
    protected function salesByDay(Carbon $start, Carbon $end): array
    {
        $rows = Transaction::query()
            ->where('status', '!=', Transaction::STATUS_CANCELLED)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'paid_amount']);

        return $rows
            ->groupBy(fn (Transaction $tx): string => $tx->created_at->format('Y-m-d'))
            ->map(fn (Collection $group): int => (int) $group->sum('paid_amount'))
            ->all();
    }
}
