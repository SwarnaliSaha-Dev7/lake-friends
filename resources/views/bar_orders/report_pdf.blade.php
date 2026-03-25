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

        .stats-table { width: calc(100% - 40px); margin: 0 20px 14px; border-collapse: separate; border-spacing: 8px 0; }
        .stat-cell   { border-radius: 6px; padding: 10px 12px; }
        .stat-label  { font-size: 8px; color: #fff; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.04em; }
        .stat-value  { font-size: 15px; font-weight: 700; color: #fff; margin-top: 3px; }

        .table-wrap { padding: 0 20px; }

        table.main { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.main thead th {
            background: #1e3a5f; color: #fff;
            padding: 6px 8px; text-align: left;
            font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em;
            white-space: nowrap;
        }
        table.main thead th.r { text-align: right; }
        table.main tbody td   { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        table.main tbody tr:nth-child(even) { background: #f8fafc; }
        table.main tbody tr:last-child td { border-bottom: none; }
        table.main tfoot td {
            background: #1e3a5f; color: #fff;
            padding: 6px 8px; font-weight: 700; font-size: 9.5px;
        }
        .text-right { text-align: right; }
        .text-muted { color: #94a3b8; }

        .footer { margin-top: 12px; padding: 8px 20px; border-top: 1px solid #e2e8f0; }
        .footer small { font-size: 8px; color: #94a3b8; }
        .f-right { float: right; }
    </style>
</head>
<body>

<div class="header" style="text-align:center;">
    <p style="margin:0 0 5px;"><img src="{{ public_path($clubDetails->logo) }}" alt="Club Logo" style="max-width:50px; max-height:50px;"></p>
    <p style="margin:0 0 2px; font-size:13px; font-weight:700; color:#fff; text-align:center;">{{ $clubDetails->name }}</p>
    <p style="margin:0 0 10px; font-size:8px; color:rgba(255,255,255,0.7); text-align:center;">{{ $clubDetails->address }}</p>
    <h1>Bar Order Report</h1>
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
        <td class="stat-cell" style="background:#0288d1;">
            <div class="stat-label">Top Selling Liquor</div>
            <div class="stat-value" style="font-size:12px;">{{ $topSellingLiquor }}</div>
        </td>
        <td class="stat-cell" style="background:#388e3c;">
            <div class="stat-label">Total Selling</div>
            <div class="stat-value">&#8377;{{ number_format($totalSelling, 2) }}</div>
        </td>
    </tr>
</table>

<div class="table-wrap">
@php
    $hasRows    = false;
    $grandTotal = 0;
    $rowBuffer  = [];
    foreach ($orders as $order) {
        foreach ($order->items->whereIn('unit', ['ml', 'btl']) as $item) {
            $hasRows      = true;
            $isBeer       = $item->unit === 'btl';
            $offerApplied = $item->offer_applied;
            $grandTotal  += $item->total_amount;

            if ($isBeer && $offerApplied && ($offerApplied['type_slug'] ?? '') === 'b1g1') {
                $buyQty   = $offerApplied['buy_qty'] ?? 1;
                $getQty   = $offerApplied['get_qty'] ?? 1;
                $setSize  = $buyQty + $getQty;
                $sets     = $setSize > 0 ? intdiv($item->quantity, $setSize) : 0;
                $volLabel = ($sets * $buyQty) . ' BTL + ' . ($sets * $getQty) . ' Free = ' . $item->quantity . ' BTL';
            } else {
                $volLabel = $isBeer
                    ? $item->quantity . ' BTL'
                    : (($item->metadata['volume_ml'] ?? '?') . 'ml × ' . $item->quantity);
            }

            $itemLabel = $item->foodItem->name ?? '—';
            if ($offerApplied) {
                $offerTag = match($offerApplied['type_slug'] ?? '') {
                    'b1g1'       => ' [B' . ($offerApplied['buy_qty'] ?? 1) . 'G' . ($offerApplied['get_qty'] ?? 1) . ']',
                    'percentage' => ' [' . ($offerApplied['discount_value'] ?? '') . '% OFF]',
                    'flat'       => ' [Rs ' . ($offerApplied['discount_value'] ?? '') . ' OFF]',
                    default      => '',
                };
                $itemLabel .= $offerTag;
            }

            $rowBuffer[] = [
                'order_no'   => $order->order_no,
                'member'     => $order->member->name ?? '—',
                'datetime'   => $order->created_at->format('d M Y, h:i A'),
                'item'       => $itemLabel,
                'volume'     => $volLabel,
                'unit_price' => number_format($item->unit_price, 2),
                'amount'     => number_format($item->total_amount, 2),
            ];
        }
    }
@endphp

@if(!$hasRows)
    <p style="text-align:center; padding:30px; color:#94a3b8;">No bar orders found for the selected period.</p>
@else
    <table class="main">
        <thead>
            <tr>
                <th>#</th>
                <th>Order No</th>
                <th>Member</th>
                <th>Date &amp; Time</th>
                <th>Item</th>
                <th>Volume</th>
                <th class="r">Unit Price</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rowBuffer as $i => $r)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td style="white-space:nowrap;">{{ $r['order_no'] }}</td>
                <td>{{ $r['member'] }}</td>
                <td class="text-muted" style="white-space:nowrap;">{{ $r['datetime'] }}</td>
                <td>{{ $r['item'] }}</td>
                <td>{{ $r['volume'] }}</td>
                <td class="text-right">&#8377;{{ $r['unit_price'] }}</td>
                <td class="text-right">&#8377;{{ $r['amount'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Grand Total</td>
                <td class="text-right">&#8377;{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
</div>

<div class="footer">
    <small class="f-right">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</small>
    <small>{{ $clubDetails->name }} &nbsp;|&nbsp; Bar Order Report</small>
</div>

</body>
</html>
