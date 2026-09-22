<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $stockOut->id }} - GR_merch</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/Logo-TAG-favicon-16x16px.png') }}">
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f4;
            color: #17212b;
        }

        .invoice {
            width: min(210mm, calc(100% - 2rem));
            min-height: 297mm;
            margin: 2rem auto;
            padding: 18mm;
            position: relative;
            background: #ffffff url('{{ asset('assets/images/template-invoice.png') }}') center / cover no-repeat;
            box-shadow: 0 8px 24px rgba(20, 34, 48, 0.16);
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .invoice-header,
        .invoice-meta,
        .invoice-total {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .invoice-header {
            align-items: flex-start;
            padding-bottom: 1.25rem;
            border-bottom: 3px solid #d70b18;
        }

        .brand {
            display: block;
            width: min(170px, 25vw);
            height: auto;
            max-height: 45px;
            object-fit: contain;
            object-position: left center;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 0.35rem;
            font-size: 1.7rem;
        }

        .muted {
            color: #667381;
        }

        .invoice-meta {
            margin: 1.5rem 0;
        }

        .meta-block {
            flex: 1;
        }

        .meta-block strong {
            display: block;
            margin-bottom: 0.35rem;
        }

        table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.8rem 0.6rem;
            border-bottom: 1px solid #dbe1e6;
            text-align: left;
        }

        th {
            background: #f3f5f7;
            color: #4e5b68;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .number {
            text-align: right;
        }

        .invoice-total {
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .total-box {
            min-width: 250px;
            padding: 1rem;
            border: 1px solid #dbe1e6;
            border-radius: 4px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.25rem 0;
        }

        .total-row.final {
            margin-top: 0.5rem;
            padding-top: 0.7rem;
            border-top: 2px solid #d70b18;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .notes {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dbe1e6;
        }

        .print-button {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 0.7rem 1rem;
            border: 0;
            border-radius: 4px;
            background: #d70b18;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
        }

        .topbar,
        .invoice-heading,
        .summary,
        .footer-row {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .topbar {
            align-items: flex-start;
            min-height: 35mm;
        }

        .topbar-left {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .gr-brand {
            display: block;
            width: min(170px, 25vw);
            height: auto;
            max-height: 45px;
            object-fit: contain;
            object-position: right top;
        }

        .brand-caption,
        .document-number,
        .document-date,
        .branch,
        .transaction-info {
            margin: 0.3rem 0 0;
            color: #667381;
            font-size: 0.78rem;
        }

        .document-title {
            width: 100%;
            margin-top: 1.2rem;
            padding-top: 0.75rem;
            border-top: 2px solid #e30613;
            text-align: left;
        }

        .document-title h1 {
            margin: 0;
            font-size: 1.7rem;
            letter-spacing: 0.08em;
        }

        .document-title h1::before,
        .section-title::before {
            content: '/';
            margin-right: 0.35rem;
            color: #e30613;
        }

        .document-number strong {
            color: #e30613;
            font-size: 1.1rem;
        }

        .invoice-heading {
            align-items: flex-end;
            padding-bottom: 0.8rem;
        }

        .branch strong,
        .transaction-info strong {
            color: #17212b;
        }

        .section-title {
            margin: 1.35rem 0 0.55rem;
            font-size: 1rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .items-table th {
            padding: 0.55rem 0.75rem;
            background: rgba(232, 235, 239, 0.88);
            color: #17212b;
        }

        .items-table td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .items-table th:first-child {
            border-radius: 5px 0 0 5px;
        }

        .items-table th:last-child {
            border-radius: 0 5px 5px 0;
        }

        .item-name {
            font-weight: 700;
            text-transform: uppercase;
        }

        .item-code {
            display: block;
            margin-top: 0.2rem;
            color: #667381;
            font-size: 0.75rem;
        }

        .summary {
            margin-top: 1.35rem;
        }

        .summary-box {
            width: 100%;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
            padding: 0.5rem 0;
            font-size: 0.88rem;
        }

        .summary-row span:last-child {
            min-width: 170px;
            text-align: right;
        }

        .summary-row.total-before {
            border-top: 1px solid #d7dce1;
            padding-top: 0.8rem;
            font-weight: 700;
        }

        .summary-row.grand-total {
            margin-top: 0.65rem;
            padding: 0.8rem 1rem;
            border-radius: 5px;
            background: linear-gradient(105deg, rgba(232, 235, 239, 0.88) 0 52%, #e30613 52%);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .summary-row.grand-total span:first-child {
            color: #17212b;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .notes-box {
            padding: 0.9rem 1rem;
            border-radius: 5px;
            background: rgba(232, 235, 239, 0.88);
            color: #667381;
            font-size: 0.84rem;
        }

        .notes-box p {
            margin: 0;
        }

        .notes-box p + p {
            margin-top: 0.55rem;
            padding-top: 0.55rem;
            border-top: 1px solid #d7dce1;
        }

        .notes-box strong {
            color: #17212b;
        }

        .footer-row {
            align-items: flex-end;
            position: absolute;
            right: 18mm;
            bottom: 12mm;
            left: 18mm;
            margin: 0;
            padding: 0;
        }

        .footer-slogan {
            color: #ffffff;
            font-size: 1rem;
            font-style: italic;
            font-weight: 800;
            letter-spacing: 0.08em;
            
        }

        .footer-slogan span {
            color: #e30613;
        }

        .footer-detail {
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.7rem;
            text-align: right;
        }

        @media (max-width: 600px) {
            .invoice {
                width: 100%;
                min-height: 100vh;
                margin: 0;
                padding: 1.25rem;
            }

            .invoice-header,
            .invoice-meta,
            .topbar,
            .invoice-heading,
            .footer-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .document-title,
            .footer-detail {
                text-align: left;
            }

            .footer-row {
                right: 1.25rem;
                bottom: 1.25rem;
                left: 1.25rem;
            }

            .print-button {
                position: static;
                display: block;
                margin: 1rem auto;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                background: #ffffff;
            }

            .invoice {
                width: 100%;
                min-height: 297mm;
                margin: 0;
                padding: 18mm;
                box-shadow: none;
                background-size: cover;
                background-position: center top;
            }

            .footer-row {
                right: 18mm;
                bottom: 10mm;
                left: 18mm;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button type="button" class="print-button" onclick="window.print()">Cetak Invoice</button>

    @php
        $subtotal = $stockOuts->sum(function ($row) {
            $unitPrice = $row->harga_jual !== null ? (float) $row->harga_jual : ($row->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : 0);

            return $unitPrice * (int) $row->jumlah;
        });
        $voucherAmounts = ['500k' => 500000, '1jt' => 1000000];
        $voucherAmount = $stockOut->jenis === 'DO' ? ($voucherAmounts[$stockOut->voucher ?? ''] ?? 0) : 0;
        $hasTransactionTotal = $stockOut->jenis === 'penjualan' || $stockOut->jenis === 'DO';
        $discountAmount = $stockOut->jenis === 'DO' ? $voucherAmount : ($hasTransactionTotal ? $subtotal - $stockOuts->sum(fn ($row) => (float) ($row->total ?? 0)) : null);
        $totalAkhir = $stockOut->jenis === 'DO'
            ? max(0, $subtotal - $voucherAmount)
            : $stockOuts->sum(fn ($row) => $row->total !== null ? (float) $row->total : 0);
    @endphp

    <main class="invoice">
        <header class="topbar">
            <div class="topbar-left">
                <img src="{{ asset('assets/images/Logo TAG.png') }}" alt="TAG Tunas Auto Graha" class="brand">
                {{-- <p class="brand-caption">Authorized Toyota Dealer</p> --}}
                <div class="document-title">
                    <h1>INVOICE</h1>
                    <p class="document-number">No. <strong>{{ str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
                    <p class="document-date">Tanggal: {{ $stockOut->tanggal?->format('d-m-Y') ?? '-' }}</p>
                </div>
            </div>
            <img src="{{ asset('assets/images/gr-300-px.png') }}" alt="GR Toyota Gazoo Racing" class="gr-brand">
        </header>

        <section class="invoice-heading">
            <p class="branch"><strong>Cabang:</strong> {{ $stockOut->cabang->nama_cabang ?? '-' }}</p>
            <p class="branch"><strong>Customer:</strong> {{ $stockOut->nama_customer ?: '-' }}</p>
            @if($stockOut->jenis === 'penjualan')
                <p class="branch"><strong>No. Telpon:</strong> {{ $stockOut->nomor_telepon ?: '-' }}</p>
            @else
                <p class="branch"><strong>No. SPK:</strong> {{ $stockOut->nomor_spk ?: '-' }}</p>
            @endif
            {{-- <p class="transaction-info"><strong>Jenis Transaksi:</strong> {{ ucfirst($stockOut->jenis) }} &nbsp; | &nbsp; <strong>Status:</strong> {{ ucfirst($stockOut->status) }}</p> --}}
        </section>

        <section>       
            <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Barang</th>
                    <th class="number">Harga</th>
                    <th class="number">Jumlah</th>
                    <th class="number">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockOuts as $row)
                    <tr>
                        <td>
                            <span class="item-name">{{ $row->item->nama_items ?? '-' }}</span>
                            <span class="item-code">Kode: {{ $row->item->kode_items ?? '-' }}</span>
                        </td>
                        @php($unitPrice = $row->harga_jual !== null ? (float) $row->harga_jual : ($row->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : null))
                        <td class="number">{{ $unitPrice !== null ? 'Rp '.number_format($unitPrice, 0, ',', '.') : '-' }}</td>
                        <td class="number">{{ $row->jumlah }}</td>
                        <td class="number">{{ $unitPrice !== null ? 'Rp '.number_format($unitPrice * $row->jumlah, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </section>

        <section class="summary">
            <div class="summary-box">
                <div class="summary-row total-before">
                    <span>Total</span>
                    <span>{{ $hasTransactionTotal ? 'Rp '.number_format($subtotal, 0, ',', '.') : 'Non-penjualan' }}</span>
                </div>
                <div class="summary-row">
                    <span>{{ $stockOut->jenis === 'DO' ? 'Potongan' : 'Discount '.rtrim(rtrim(number_format($stockOut->discount ?? 0, 2, '.', ''), '0'), '.').'%' }}</span>
                    <span>{{ $discountAmount !== null ? 'Rp '.number_format($discountAmount, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="summary-row grand-total">
                    <span>Total Keseluruhan</span>
                    <span>{{ $hasTransactionTotal ? 'Rp '.number_format($totalAkhir, 0, ',', '.') : 'Non-penjualan' }}</span>
                </div>
            </div>
        </section>

        <section class="notes">
            <h2 class="section-title">Keterangan</h2>
            <div class="notes-box">
                <p><strong>Jenis Transaksi:</strong> {{ ucfirst($stockOut->jenis) }}</p>
                <p><strong>Catatan:</strong> {{ $stockOut->keterangan ?: '-' }}</p>
            @if(in_array($stockOut->jenis, ['penjualan', 'DO'], true))
                <p><strong>Dilakukan oleh:</strong> {{ $stockOut->user->name ?? '-' }}{{ $stockOut->approved_at ? ' pada '.$stockOut->approved_at->format('d-m-Y H:i') : '' }}</p>
            @elseif($stockOut->approver)
                <p><strong>Disetujui oleh:</strong> {{ $stockOut->approver->name }}{{ $stockOut->approved_at ? ' pada '.$stockOut->approved_at->format('d-m-Y H:i') : '' }}</p>
            @else
                <p><strong>Diproses oleh:</strong> {{ $stockOut->user->name ?? '-' }}</p>
            @endif
            </div>
        </section>

        <footer class="footer-row">
            <div class="footer-slogan"><span>TAG</span> Memberi Lebih ...</div>
            {{-- <div class="footer-detail">{{ $stockOut->cabang->nama_cabang ?? 'TAG Head Office' }}<br>GR merchandise</div> --}}
        </footer>
    </main>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
