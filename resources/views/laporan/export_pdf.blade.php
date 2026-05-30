<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 18px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #111827;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .header {
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #d1d5db;
        }

        .title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 6px 0;
            letter-spacing: 0.2px;
        }

        .meta {
            font-size: 9px;
            color: #6b7280;
        }

        .section {
            margin-top: 14px;
            padding: 10px 12px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f3f4f6;
            color: #374151;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        tbody tr:hover td {
            background: #f9fafb;
        }

        .empty {
            color: #6b7280;
            font-style: italic;
            padding: 2px 0 0;
        }

        .header-meta {
            display: table;
            width: 100%;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
        }

        .label {
            color: #6b7280;
        }

        .value {
            color: #111827;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-meta">
            <div class="header-left">
                <div class="title">{{ $title }}</div>
                <div class="meta">Laporan ringkas operasional dan keuangan</div>
            </div>
            <div class="header-right meta">
                <div><span class="label">Outlet:</span> <span
                        class="value">{{ $meta['store'] ?? 'Semua Outlet' }}</span></div>
                <div><span class="label">Periode:</span> <span class="value">{{ $meta['period'] ?? '-' }}</span></div>
                <div><span class="label">Dicetak:</span> <span class="value">{{ $generatedAt }}</span></div>
            </div>
        </div>
    </div>

    @foreach ($rows as $section)
        <div class="section">
            <div class="section-title">{{ $section['title'] }}</div>
            @if (empty($section['rows']))
                <div class="empty">Tidak ada data.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            @foreach ($section['headers'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</body>

</html>
