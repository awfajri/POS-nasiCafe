<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsReportExport;
use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Halaman Laporan Keuangan: ringkasan + tabel transaksi ber-paginasi.
     */
    public function index(ReportFilterRequest $request): View
    {
        [$start, $end] = $this->range($request);

        $summary = $this->reportService->summary($start, $end);

        $transactions = $this->reportService
            ->rowsQuery($start, $end)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('owner.reports.index', [
            'summary' => $summary,
            'transactions' => $transactions,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * Unduh laporan sebagai Excel (.xlsx).
     */
    public function exportExcel(ReportFilterRequest $request): BinaryFileResponse
    {
        [$start, $end] = $this->range($request);

        $filename = "laporan-{$start->format('Y-m-d')}-sd-{$end->format('Y-m-d')}.xlsx";

        return Excel::download(new TransactionsReportExport($start, $end), $filename);
    }

    /**
     * Unduh laporan sebagai PDF (DomPDF, CSS inline).
     */
    public function exportPdf(ReportFilterRequest $request)
    {
        [$start, $end] = $this->range($request);

        $rows = $this->reportService->rowsQuery($start, $end)->orderBy('created_at')->get();
        $summary = $this->reportService->summary($start, $end);

        $pdf = Pdf::loadView('owner.reports.pdf', [
            'rows' => $rows,
            'summary' => $summary,
            'start' => $start,
            'end' => $end,
        ]);

        $filename = "laporan-{$start->format('Y-m-d')}-sd-{$end->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Normalisasi rentang tanggal dari request.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function range(ReportFilterRequest $request): array
    {
        $start = Carbon::parse($request->validated('start_date'))->startOfDay();
        $end = Carbon::parse($request->validated('end_date'))->endOfDay();

        return [$start, $end];
    }
}
