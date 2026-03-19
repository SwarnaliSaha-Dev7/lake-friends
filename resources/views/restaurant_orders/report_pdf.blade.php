<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2d2d2d; }

        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #0288d1; padding-bottom: 10px; }
        .header h1 { font-size: 18px; color: #0288d1; font-weight: bold; }
        .header p  { font-size: 11px; color: #666; margin-top: 3px; }

        .summary-cards { display: table; width: 100%; margin-bottom: 16px; border-spacing: 6px; }
        .card { display: table-cell; width: 25%; padding: 10px 14px; border-radius: 6px; color: #fff; }
        .card .card-label { font-size: 9px; opacity: 0.85; margin-bottom: 4px; }
        .card .card-value { font-size: 16px; font-weight: bold; }
        .card-blue   { background: #0288d1; }
        .card-green  { background: #388e3c; }
        .card-orange { background: #e65100; }
        .card-purple { background: #7b1fa2; }

        .date-group { margin-bottom: 14px; }
        .date-heading { background: #e3f2fd; color: #0288d1; font-weight: bold; font-size: 10px;
                        padding: 5px 8px; border-left: 3px solid #0288d1; margin-bottom: 4px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #97A0AC; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: bold; white-space: nowrap; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 5px 8px; font-size: 9.5px; border-bottom: 1px solid #e8e8e8; }
        .subtotal-row td { background: #e8f4fd; font-weight: bold; font-size: 9.5px; border-top: 1px solid #b3d9f5; }
        tfoot tr { background: #0288d1; color: #fff; }
        tfoot td { padding: 7px 8px; font-size: 10px; font-weight: bold; }

        .text-right { text-align: right; }
        .footer { margin-top: 14px; text-align: right; font-size: 9px; color: #999; }

        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success  { background: #e8f5e9; color: #2e7d32; }
        .badge-primary  { background: #e3f2fd; color: #1565c0; }
        .badge-warning  { background: #fff8e1; color: #f57f17; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Restaurant Order Report</h1>
        <p>
            Period:
            @if($startDate === $endDate)
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            @else
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @endif
        </p>
        <p style="color:#999;font-size:9px;">Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; Excludes cancelled orders</p>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-cards">
        <div class="card card-blue">
            <div class="card-label">Total Orders</div>
            <div class="card-value">{{ $totalOrders }}</div>
        </div>
        <div class="card card-green">
            <div class="card-label">Total Revenue</div>
            <div class="card-value">Rs {{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card card-orange">
            <div class="card-label">Total Discount</div>
            <div class="card-value">Rs {{ number_format($totalDiscount, 2) }}</div>
        </div>
        <div class="card card-purple">
            <div class="card-label">Avg Order Value</div>
            <div class="card-value">Rs {{ number_format($avgOrder, 2) }}</div>
        </div>
    </div>

    {{-- Date-wise breakdown --}}
    @foreach($byDate as $date => $dayOrders)
        @php
            $dayRevenue  = $dayOrders->sum('net_amount');
            $dayDiscount = $dayOrders->sum('discount_amount');
        @endphp
        <div class="date-group">
            <div class="date-heading">
                {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
                &nbsp;&nbsp;|&nbsp;&nbsp; {{ $dayOrders->count() }} orders
                &nbsp;&nbsp;|&nbsp;&nbsp; Revenue: Rs {{ number_format($dayRevenue, 2) }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Member</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayOrders as $order)
                        <tr>
                            <td>{{ $order->order_no }}</td>
                            <td>{{ $order->member->name ?? '—' }}</td>
                            <td>{{ $order->created_at->format('h:i A') }}</td>
                            <td>
                                @if($order->status === 'delivered')
                                    <span class="badge badge-primary">Delivered</span>
                                @elseif($order->status === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="text-right">Rs {{ number_format($order->taxable_amount, 2) }}</td>
                            <td class="text-right">Rs {{ number_format($order->discount_amount, 2) }}</td>
                            <td class="text-right">Rs {{ number_format($order->net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tr class="subtotal-row">
                    <td colspan="4">Day Total</td>
                    <td class="text-right"></td>
                    <td class="text-right">Rs {{ number_format($dayDiscount, 2) }}</td>
                    <td class="text-right">Rs {{ number_format($dayRevenue, 2) }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    {{-- Grand Total --}}
    <table style="margin-top:8px;">
        <tfoot>
            <tr>
                <td colspan="5">Grand Total ({{ $totalOrders }} orders)</td>
                <td class="text-right">Rs {{ number_format($totalDiscount, 2) }}</td>
                <td class="text-right">Rs {{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Kolkata Lake Friends Club &mdash; Confidential</div>

</body>
</html>
