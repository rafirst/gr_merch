@php
    $invoiceAsset = static fn (string $path): string => ($isPdf ?? false)
        ? str_replace('\\', '/', public_path($path))
        : asset($path);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $stockOut->id }} - GR_merch</title>
    @unless($isPdf ?? false)
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/Logo-TAG-favicon-16x16px.png') }}?v={{ filemtime(public_path('assets/images/Logo-TAG-favicon-16x16px.png')) }}-2">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    @endunless
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
            background: #ffffff url('{{ $invoiceAsset('assets/images/invoice-gr-template.png') }}') center / cover no-repeat;
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

        .invoice-actions {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }

        .print-button,
        .download-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border: 0;
            border-radius: 4px;
            background: #d70b18;
            color: #ffffff;
            cursor: pointer;
            font-size: 1rem;
        }

        .download-button {
            background: #263746;
            color: #ffffff;
            text-decoration: none;
        }

        .invoice-heading,
        .summary,
        .footer-row {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .topbar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: start;
            gap: 1rem;
            min-height: 35mm;
        }

        .topbar-left {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 0;
        }

        .brand,
        .gr-brand {
            display: block;
            width: min(140px, 100%);
            height: auto;
            max-height: 45px;
            object-fit: contain;
        }

        .gr-brand {
            justify-self: center;
            object-position: center top;
        }

                .topbar-middle {
                    display: flex;
                    justify-content: center;
                }

                .topbar-right {
                    display: flex;
                    justify-content: flex-end;
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
            position: relative;
            width: 100%;
            margin-top: 2.5rem;
            padding-top: 1.9rem;
            text-align: left;
        }

        .document-title::before {
            position: absolute;
            top: 0;
            left: 0;
            width: calc(300% + 2rem);
            border-top: 2px solid #e30613;
            content: '';
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
            margin-top: 1rem;
            padding-bottom: 1rem;
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
            padding: 0.9rem 0.75rem;
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
            margin-top: 1.8rem;
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

        .transfer-info {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            width: min(100%, 365px);
            min-height: 116px;
            margin-top: 0.75rem;
            padding: 0.75rem 3.5rem 0.75rem 0.75rem;
            position: relative;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .payment-title {
            margin-bottom: 0.35rem;
        }

        .payment-intro {
            margin: 0;
            color: #667381;
            font-size: 0.84rem;
        }

        .transfer-info-shape {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .transfer-info-icon {
            display: flex;
            position: relative;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #dcecff;
            z-index: 1;
        }

        .transfer-info-body {
            position: relative;
            min-width: 0;
            z-index: 1;
        }

        .transfer-info-label {
            margin: 0;
            color: #667381;
            font-size: 0.8rem;
        }

        .transfer-info-account {
            max-width: 8.5rem;
            margin: 0.2rem 0 0;
            color: #17212b;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .transfer-info-owner {
            margin: 0.15rem 0 0;
            color: #4e5b68;
            font-size: 0.84rem;
        }

        .footer-row {
            position: absolute;
            right: 0;
            bottom: 5mm;
            left: 10mm;
            margin: 0;
            padding: 0;
            line-height: 0;
        }

        .footer-image {
            display: block;
            width: 95%;
            height: auto;
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
            .invoice-heading {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.5rem;
                min-height: auto;
            }

            .brand,
            .gr-brand {
                max-height: 35px;
            }

            .document-title {
                text-align: left;
            }

            .document-title::before {
                width: calc(300% + 1rem);
            }

            .invoice-actions {
                position: static;
                justify-content: center;
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
                font-family: Arial, sans-serif;
            }

            .invoice {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 18mm;
                box-shadow: none;
                background-position: center top;
                background-size: cover;
            }

            .footer-row {
                right: 0;
                bottom: 5mm;
                left: 10mm;
            }

            .invoice-actions {
                display: none;
            }

            body.pdf-render .invoice {
                width: 174mm;
                min-height: 261mm;
                padding: 18mm;
                background-image: url('{{ $invoiceAsset('assets/images/invoice-gr-template.png') }}');
                background-size: 100% 100%;
                background-repeat: no-repeat;
            }

            body.pdf-render .topbar {
                display: table;
                width: 100%;
                table-layout: fixed;
            }

            body.pdf-render .topbar-left,
            body.pdf-render .topbar-middle,
            body.pdf-render .topbar-right {
                display: table-cell;
                width: 33.333%;
                vertical-align: top;
            }

            body.pdf-render .topbar-middle {
                text-align: center;
            }

            body.pdf-render .topbar-right {
                text-align: right;
            }

            body.pdf-render .brand,
            body.pdf-render .gr-brand {
                width: 140px;
                max-height: 45px;
            }

            body.pdf-render .gr-brand {
                display: block;
                margin: 0 auto;
            }

            body.pdf-render .topbar-right .brand {
                margin-left: auto;
            }

            body.pdf-render .document-title::before {
                width: 300%;
            }

            body.pdf-render .invoice-heading {
                display: table;
                width: 100%;
                table-layout: fixed;
            }

            body.pdf-render .invoice-heading > p {
                display: table-cell;
                vertical-align: top;
                padding-right: 4mm;
            }

            body.pdf-render .invoice-heading > p:first-child {
                width: 27%;
            }

            body.pdf-render .invoice-heading > p:nth-child(2) {
                width: 43%;
            }

            body.pdf-render .invoice-heading > p:last-child {
                width: 30%;
                padding-right: 0;
            }

            body.pdf-render .invoice-heading .branch strong {
                display: block;
                margin-bottom: 1mm;
                line-height: 1.2;
            }

            body.pdf-render .items-table {
                table-layout: fixed;
            }

            body.pdf-render .items-table th:first-child,
            body.pdf-render .items-table td:first-child {
                width: 48%;
            }

            body.pdf-render .items-table th:nth-child(2),
            body.pdf-render .items-table td:nth-child(2),
            body.pdf-render .items-table th:last-child,
            body.pdf-render .items-table td:last-child {
                width: 20%;
            }

            body.pdf-render .items-table th:nth-child(3),
            body.pdf-render .items-table td:nth-child(3) {
                width: 12%;
            }

            body.pdf-render .summary-row {
                display: table;
                width: 100%;
                table-layout: fixed;
            }

            body.pdf-render .summary-row span {
                display: table-cell;
                vertical-align: middle;
            }

            body.pdf-render .summary-row span:last-child {
                text-align: right;
            }

            body.pdf-render .summary-row.grand-total {
                background: none;
                padding: 0;
                font-family: "DejaVu Sans", sans-serif;
                font-size: 0.88rem;
                font-weight: 700;
                line-height: 1.35;
            }

            body.pdf-render .summary-row.grand-total span:first-child {
                width: 52%;
                border-radius: 5px 0 0 5px;
                background: #e8ebef;
                padding-top: 0.7rem;
                padding-bottom: 0.7rem;
                padding-left: 0.7rem;
                font-family: "DejaVu Sans", sans-serif;
                font-size: 0.88rem;
                font-weight: 700;
                line-height: 1.35;
                white-space: nowrap;
            }

            body.pdf-render .summary-row.grand-total span:last-child {
                width: 48%;
                border-radius: 0 5px 5px 0;
                background: #e30613;
                padding-top: 0.7rem;
                padding-bottom: 0.7rem;
                padding-right: 0.7rem;
                color: #ffffff;
                font-family: "DejaVu Sans", sans-serif;
                font-size: 0.88rem;
                font-weight: 700;
                line-height: 1.35;
            }
        }
    </style>
</head>
<body class="{{ ($isPdf ?? false) ? 'pdf-render' : '' }}">
    @unless($isPdf ?? false)
        <div class="invoice-actions">
            <button type="button" class="print-button" onclick="window.print()" title="Cetak Invoice" aria-label="Cetak Invoice">
                <i class="fas fa-print" aria-hidden="true"></i>
            </button>
            <a href="{{ route('stockout.invoice.download', $stockOut, false) }}" class="download-button" title="Download Invoice" aria-label="Download Invoice">
                <i class="fas fa-download" aria-hidden="true"></i>
            </a>
        </div>
    @endunless

    @php
        $subtotal = $stockOuts->sum(function ($row) {
            $unitPrice = $row->harga_jual !== null ? (float) $row->harga_jual : ($row->jenis === 'DO' ? (float) ($row->item->harga_items ?? 0) : 0);

            return $unitPrice * (int) $row->jumlah;
        });
        $hasTransactionTotal = $stockOut->jenis === 'penjualan' || $stockOut->jenis === 'DO';
        $ppnAmount = $hasTransactionTotal ? (int) round($subtotal * 0.11) : null;
        $discountAmount = $stockOut->jenis === 'DO' ? 0 : ($hasTransactionTotal ? $subtotal - $stockOuts->sum(fn ($row) => (float) ($row->total ?? 0)) : null);
        $discountLabel = $stockOut->jenis === 'DO'
            ? 'Potongan (0%)'
            : ($stockOut->jenis === 'penjualan' ? $stockOut->discountLabel() : 'Discount '.rtrim(rtrim(number_format($stockOut->discount ?? 0, 2, '.', ''), '0'), '.').'%');
        $totalAkhir = $stockOut->jenis === 'DO'
            ? $subtotal + ($ppnAmount ?? 0)
            : $stockOuts->sum(fn ($row) => $row->total !== null ? (float) $row->total : 0) + ($ppnAmount ?? 0);

        // Rekening transfer per cabang, sama dengan data di halaman create Barang Keluar.
        $transferAccounts = [
            'PLG' => 'BCA 0216198888',
            'POL' => 'BCA 0216198888',
            'THO' => 'BCA 0216198888',
            'PRB' => 'BCA 0218829999',
            'TME' => 'BCA 0218829999',
            'LLG' => 'BCA 0217817777',
        ];
        $transferAccount = $transferAccounts[$stockOut->cabang->kode_cabang ?? ''] ?? null;
        $showTransferInfo = $transferAccount !== null && $stockOut->jenis_pembayaran === 'transfer';
    @endphp

    <main class="invoice">
        <header class="topbar">
            <div class="topbar-left">
                <img src="{{ $invoiceAsset('assets/images/Logo-Toyota.png') }}" alt="Toyota" class="brand">
                {{-- <p class="brand-caption">Authorized Toyota Dealer</p> --}}
                <div class="document-title">
                    <h1>INVOICE</h1>
                    <p class="document-number">No. <strong>{{ str_pad((string) $stockOut->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
                    <p class="document-date">Tanggal: {{ $stockOut->tanggal?->format('d-m-Y') ?? '-' }}</p>
                </div>
            </div>
            <div class="topbar-middle">
                <img src="{{ $invoiceAsset('assets/images/gr-300-px.png') }}" alt="GR Toyota Gazoo Racing" class="gr-brand">
            </div>
            <div class="topbar-right">
                <img src="{{ $invoiceAsset('assets/images/Logo TAG.png') }}" alt="TAG Tunas Auto Graha" class="brand">
            </div>
        </header>

        <section class="invoice-heading">
            <p class="branch"><strong>Cabang:</strong> {{ $stockOut->cabang->nama_cabang ?? '-' }}</p>
            @if($stockOut->jenis === 'penjualan')
                <p class="branch"><strong>Customer:</strong> {{ $stockOut->nama_customer ?: '-' }}</p>
                <p class="branch"><strong>No. Telpon:</strong> {{ $stockOut->nomor_telepon ?: '-' }}</p>
            @elseif($stockOut->jenis === 'DO')
                <p class="branch"><strong>No. SPK:</strong> {{ $stockOut->nomor_spk ?: '-' }}<br><strong>Paket Bundling:</strong> {{ match ($stockOut->paket_bundling) { 'paket_a' => 'Paket A', 'paket_b' => 'Paket B', 'paket_c' => 'Paket C', default => '-' } }}</p>
            @elseif($stockOut->jenis === 'request')
                <p class="branch"><strong>No. IM:</strong> {{ $stockOut->nomor_im ?: '-' }}</p>
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
                    <span>Subtotal</span>
                    <span>{{ $hasTransactionTotal ? 'Rp '.number_format($subtotal, 0, ',', '.') : 'Non-penjualan' }}</span>
                </div>
                <div class="summary-row">
                    <span>{{ $discountLabel }}</span>
                    <span>{{ $discountAmount !== null ? 'Rp '.number_format($discountAmount, 0, ',', '.') : '-' }}</span>
                </div>
                @if($hasTransactionTotal)
                    <div class="summary-row">
                        <span>PPN 11%</span>
                        <span>Rp {{ number_format($ppnAmount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="summary-row grand-total">
                    <span>Total Keseluruhan</span>
                    <span>{{ $hasTransactionTotal ? 'Rp '.number_format($totalAkhir, 0, ',', '.') : 'Non-penjualan' }}</span>
                </div>
            </div>
        </section>

        @if($showTransferInfo)
            <h2 class="section-title payment-title">Pembayaran</h2>
            <p class="payment-intro">Silakan melakukan pembayaran melalui transfer ke:</p>
            <section class="transfer-info" aria-label="Informasi transfer bank">
                <svg class="transfer-info-shape" viewBox="0 0 365 116" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                    <path d="M 5 1 H 329.5 Q 336 1 334.3 7.3 L 306.7 108.7 Q 305 115 298.5 115 H 5 Q 1 115 1 111 V 5 Q 1 1 5 1 Z" fill="#f4f9ff" stroke="#c0c0c0" stroke-width="1"></path>
                </svg>
                <div class="transfer-info-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1565c0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" role="img" aria-hidden="true" focusable="false">
                        <path d="M3 10 12 4l9 6"></path>
                        <path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"></path>
                        <path d="M3 20h18"></path>
                    </svg>
                </div>
                <div class="transfer-info-body">
                    <p class="transfer-info-label">Transfer Bank </p>
                    <p class="transfer-info-account">{{ $transferAccount }}</p>
                    <p class="transfer-info-owner">a.n Tunas Auto Graha</p>
                </div>
            </section>
        @endif

        {{-- <section class="notes">
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
        </section> --}}

        <footer class="footer-row">
            <img src="{{ $invoiceAsset('assets/images/sosmed-tag.png') }}" alt="Media sosial TAG" class="footer-image">
        </footer>
    </main>

    @unless($isPdf ?? false)
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endunless
</body>
</html>
