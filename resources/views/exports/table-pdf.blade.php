<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 22px 24px;
        }

        body {
            color: #183248;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }

        h1 {
            color: #173d5c;
            font-size: 17px;
            margin: 0 0 4px;
        }

        .generated-at {
            color: #748697;
            font-size: 7px;
            margin-bottom: 12px;
            padding-bottom: 9px;
            border-bottom: 1px solid #cbd9e4;
        }

        .subtitle {
            color: #748697;
            font-size: 7px;
            margin-bottom: 3px;
        }

        .section-title {
            color: #24465f;
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section-icon {
            background: #24465f;
            display: inline-block;
            height: 8px;
            margin-right: 5px;
            vertical-align: middle;
            width: 8px;
        }

        table {
            border-collapse: collapse;
            table-layout: auto;
            width: 100%;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border-bottom: 1px solid #d8e3ec;
            border-right: 1px solid #d8e3ec;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #24465f;
            border-color: #49647a;
            color: #ffffff;
            font-weight: bold;
            font-size: 7px;
        }

        th.row-number,
        td.row-number {
            border-left: 1px solid #d8e3ec;
            text-align: center;
            width: 4%;
        }

        td.row-number {
            background: #f0f5f8;
            color: #35536a;
        }

        tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        tbody tr:nth-child(even) td.row-number {
            background: #eaf1f6;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">{{ $subtitle }}</div>
    <div class="generated-at">Diekspor pada {{ $generatedAt }}</div>
    <div class="section-title"><span class="section-icon"></span>{{ $sectionTitle }}</div>

    <table>
        <thead>
            <tr>
                <th class="row-number">No</th>
                @foreach ($headings as $heading)
                    <th>{{ strtoupper(str_replace('_', ' ', $heading)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="row-number">{{ $loop->iteration }}</td>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) + 1 }}">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>