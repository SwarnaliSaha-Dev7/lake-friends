<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

        .header { background: #1e3a5f; color: #fff; padding: 14px 20px; margin-bottom: 14px; }
        .header h1 { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .header p  { font-size: 9px; color: rgba(255,255,255,0.7); }

        /* stat cards — table-based for dompdf */
        .stats-table { width: calc(100% - 40px); margin: 0 20px 14px; border-collapse: separate; border-spacing: 8px 0; }
        .stat-cell { border-radius: 6px; padding: 10px 12px; }
        .stat-label { font-size: 8px; color: #fff; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.04em; }
        .stat-value { font-size: 15px; font-weight: 700; color: #fff; margin-top: 3px; }
        .stat-sub   { font-size: 8px; color: rgba(255,255,255,0.75); margin-top: 1px; }

        .table-wrap { padding: 0 20px; }

        table.main { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.main thead th {
            background: #1e3a5f; color: #fff;
            padding: 6px 8px; text-align: left;
            font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em;
            white-space: nowrap;
        }
        table.main thead th.r { text-align: right; }
        table.main tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        table.main tbody tr:nth-child(even) { background: #f8fafc; }
        table.main tbody tr:last-child td { border-bottom: none; }
        table.main tfoot td {
            background: #1e3a5f; color: #fff;
            padding: 6px 8px; font-weight: 700; font-size: 9.5px;
        }
        .text-right { text-align: right; }
        .text-muted { color: #94a3b8; }

        .offer-badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7.5px; font-weight: 700; }
        .offer-b1g1  { background: #fef3c7; color: #d97706; }
        .offer-pct   { background: #dcfce7; color: #16a34a; }
        .offer-flat  { background: #dbeafe; color: #2563eb; }

        .footer { margin-top: 12px; padding: 8px 20px; border-top: 1px solid #e2e8f0; }
        .footer small { font-size: 8px; color: #94a3b8; }
        .f-right { float: right; }
    </style>
</head>
<body>

    <div class="modal-header d-flex gap-3 justify-content-between align-items-center border-0">
        <img src="{{ public_path($clubDetails->logo) }}" alt="img" loading="lazy" fetchpriority="auto" style="max-width: 50px;">
            <p class="m-0 lh-2">{{ $clubDetails->name }}</p>
            <p class="m-0 lh-2">{{ $clubDetails->address }}</p>
    </div>

<div class="header">
    <h1>Food Report</h1>
    <p>
        Period:
        @if($startDate === $endDate)
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
        @else
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        @endif
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; Excludes cancelled orders
    </p>
</div>

{{-- Stat cards --}}
<table class="stats-table">
    <tr>
        <td class="stat-cell" style="background:#d97706;">
            <div class="stat-label">Top Selling Food</div>
            <div class="stat-value" style="font-size:12px;">{{ $topSelling['name'] ?? '—' }}</div>
            @if($topSelling)
            <div class="stat-sub">{{ number_format($topSelling['qty']) }} plates sold</div>
            @endif
        </td>
        <td class="stat-cell" style="background:#388e3c;">
            <div class="stat-label">Total Food Revenue</div>
            <div class="stat-value">&#8377;{{ number_format($totalRevenue, 2) }}</div>
        </td>
    </tr>
</table>

<div class="table-wrap">
@if(empty($rows))
    <p style="text-align:center; padding:30px; color:#94a3b8;">No food orders found for the selected period.</p>
@else
    <table class="main">
        <thead>
            <tr>
                <th>#</th>
                <th>Order No</th>
                <th>Member</th>
                <th>Date &amp; Time</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Offer</th>
                <th class="r">Unit Price</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $r)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td style="white-space:nowrap;">{{ $r['order_no'] }}</td>
                <td>{{ $r['member'] }}</td>
                <td class="text-muted" style="white-space:nowrap;">{{ $r['datetime'] }}</td>
                <td>{{ $r['item'] }}</td>
                <td>{{ $r['qty'] }}</td>
                <td>
                    @if($r['offer'])
                        @php
                            $cls = str_contains($r['offer'], '%') ? 'offer-pct' : (str_contains($r['offer'], 'B1G1') ? 'offer-b1g1' : 'offer-flat');
                        @endphp
                        <span class="offer-badge {{ $cls }}">{{ $r['offer'] }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-right">&#8377;{{ $r['unit_price'] }}</td>
                <td class="text-right">&#8377;{{ $r['amount'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right">Grand Total</td>
                <td class="text-right">&#8377;{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
</div>

<div class="footer">
    <small class="f-right">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</small>
    <small>{{ $clubDetails->name }} &nbsp;|&nbsp; Food Report</small>
</div>

</body>
</html>
