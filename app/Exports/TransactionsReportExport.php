<?php

namespace App\Exports;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected Carbon $start,
        protected Carbon $end
    ) {}

    /**
     * Query sumber data ekspor (sama dengan laporan periode).
     */
    public function query(): EloquentBuilder|Relation
    {
        return app(ReportService::class)
            ->rowsQuery($this->start, $this->end)
            ->orderBy('created_at');
    }

    /**
     * Judul kolom pada baris pertama Excel.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Kode Nota',
            'Tanggal',
            'Pelanggan',
            'Kasir',
            'Sumber',
            'Status',
            'Status Bayar',
            'Total',
            'Dibayar',
        ];
    }

    /**
     * Pemetaan satu baris transaksi.
     *
     * @param  \App\Models\Transaction  $row
     * @return array<int, string>
     */
    public function map($row): array
    {
        return [
            $this->neutralize($row->invoice_code),
            $row->created_at?->format('d/m/Y H:i') ?? '',
            $this->neutralize($row->customer_name),
            $this->neutralize($row->cashier?->name ?? '-'),
            $this->neutralize(ucfirst($row->source)),
            $this->neutralize(ucfirst($row->status)),
            $this->neutralize(ucfirst($row->payment_status)),
            (string) $row->total_amount,
            (string) $row->paid_amount,
        ];
    }

    /**
     * Netralkan sel teks yang diawali karakter berbahaya formula spreadsheet
     * (=, +, -, @) dengan memberi awalan apostrof, karena customer_name
     * berasal dari input pengguna (mencegah formula injection).
     */
    protected function neutralize(string $value): string
    {
        $trimmed = ltrim($value);

        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
