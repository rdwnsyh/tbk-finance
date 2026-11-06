<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            color: #263238;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .period {
            margin-bottom: 16px;
            color: #546e7a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #cfd8dc;
        }

        th {
            background: #f1f3f5;
            text-align: left;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        tfoot {
            background: #f1f3f5;
            font-weight: bold;
        }

        .positive {
            color: #16803c;
        }

        .negative {
            color: #c62828;
        }

        .empty {
            padding: 20px;
            color: #607d8b;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan Laba Rugi</h1>
    <div class="period">
        Periode {{ $start->translatedFormat('F Y') }} – {{ $end->translatedFormat('F Y') }}
        @if ($search !== '')
            | Filter: {{ $search }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="amount">Pendapatan</th>
                <th class="amount">Beban</th>
                <th class="amount">Laba/Rugi Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="amount">{{ number_format($row['income'], 2, ',', '.') }}</td>
                    <td class="amount">{{ number_format($row['expense'], 2, ',', '.') }}</td>
                    <td class="amount {{ $row['net'] < 0 ? 'negative' : 'positive' }}">
                        {{ number_format($row['net'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="4">Tidak ada data pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="amount">{{ number_format($report['total_income'], 2, ',', '.') }}</td>
                <td class="amount">{{ number_format($report['total_expense'], 2, ',', '.') }}</td>
                <td class="amount {{ $report['total_net'] < 0 ? 'negative' : 'positive' }}">
                    {{ number_format($report['total_net'], 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
