<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan NasiCafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #0f172a;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1b254b;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            color: #1b254b;
        }
        .brand-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-title {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #1b254b;
        }
        .doc-period {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .summary-row {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }
        .summary-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
        }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #1b254b;
            color: #ffffff;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .tabular { font-variant-numeric: tabular-nums; }
        .status-paid { color: #10b981; font-weight: bold; }
        .status-processing { color: #3b82f6; font-weight: bold; }
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-cancelled { color: #ef4444; font-weight: bold; }
        .grand-total-row td {
            border-top: 2px solid #1b254b;
            font-weight: bold;
            background: #eef2ff;
            padding: 8px;
            font-size: 10px;
        }
        .footer {
            margin-top: 18px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Kop NasiCafe -->
    <div class="header">
        <div>
            <div class="brand-name">🍛 NasiCafe</div>
            <div class="brand-sub">Sistem Point of Sale &amp; Self-Order — Laporan Keuangan</div>
        </div>
        <div>
            <div class="doc-title">LAPORAN KEUANGAN</div>
            <div class="doc-period">Periode {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</div>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="summary-row">
        <div class="summary-box">
            <div class="summary-label">Total Pemasukan (Omzet)</div>
            <div class="summary-value">{{ rupiah($summary['omzet']) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Total Transaksi</div>
            <div class="summary-value">{{ number_format($summary['total_transactions']) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Tunai</div>
            <div class="summary-value">{{ rupiah($summary['cash']) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">QRIS</div>
            <div class="summary-value">{{ rupiah($summary['qris']) }}</div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <table>
        <thead>
            <tr>
                <th style="width: 16%;">Kode Nota</th>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 15%;">Pelanggan</th>
                <th style="width: 12%;">Kasir</th>
                <th style="width: 8%;">Sumber</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 8%;">Bayar</th>
                <th class="text-right" style="width: 10%;">Total</th>
                <th class="text-right" style="width: 11%;">Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="tabular">{{ $row->invoice_code }}</td>
                    <td>{{ $row->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->cashier?->name ?? '-' }}</td>
                    <td>{{ ucfirst($row->source) }}</td>
                    <td class="status-{{ $row->status }}">{{ ucfirst($row->status) }}</td>
                    <td>{{ ucfirst($row->payment_status) }}</td>
                    <td class="text-right tabular">{{ rupiah($row->total_amount) }}</td>
                    <td class="text-right tabular">{{ rupiah($row->paid_amount) }}</td>
                </tr>
            @endforeach

            <!-- Grand Total Omzet -->
            <tr class="grand-total-row">
                <td colspan="8" class="text-right">GRAND TOTAL OMSET</td>
                <td class="text-right tabular">{{ rupiah($summary['omzet']) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh NasiCafe POS pada {{ now()->format('d M Y H:i') }} &nbsp;·&nbsp; Total {{ $rows->count() }} transaksi
    </div>
</body>
</html>
