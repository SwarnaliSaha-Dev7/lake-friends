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

        .section-heading { font-size: 12px; font-weight: bold; color: #0288d1; margin: 14px 0 6px; }

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
        .text-center { text-align: center; }
        .footer { margin-top: 14px; text-align: right; font-size: 9px; color: #999; }

        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-danger  { background: #fdecea; color: #c62828; }
    </style>
</head>
<body>

    <div class="header">
        <p style="margin:0 0 5px;"><img src="{{ public_path($clubDetails->logo) }}" alt="Club Logo" style="max-width:55px; max-height:55px;"></p>
        <p style="margin:0 0 2px; font-size:14px; font-weight:700; color:#1e293b; text-align:center;">{{ $clubDetails->name }}</p>
        <p style="margin:0 0 12px; font-size:9px; color:#64748b; text-align:center;">{{ $clubDetails->address }}</p>
        <h1>Miscellaneous Sales Report</h1>
        <p>
            Period:
            @if($startDate === $endDate)
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            @else
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @endif
        </p>
        <p style="color:#999;font-size:9px;">Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; Excludes cancelled bills from totals</p>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-cards">
        <div class="card card-blue">
            <div class="card-label">Total Bills</div>
            <div class="card-value">{{ $totalBills }}</div>
        </div>
        <div class="card card-green">
            <div class="card-label">Total Revenue</div>
            <div class="card-value">Rs {{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card card-orange">
            <div class="card-label">Total GST</div>
            <div class="card-value">Rs {{ number_format($totalGst, 2) }}</div>
        </div>
        <div class="card card-purple">
            <div class="card-label">Avg Bill Value</div>
            <div class="card-value">Rs {{ number_format($avgBill, 2) }}</div>
        </div>
    </div>

    {{-- Date-wise breakdown --}}
    @foreach($byDate as $date => $dayBills)
        @php
            $dayPaid    = $dayBills->where('status', 'paid');
            $dayRevenue = $dayPaid->sum('net_amount');
            $dayGst     = $dayPaid->sum('gst_amount');
        @endphp
        <div class="date-group">
            <div class="date-heading">
                {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
                &nbsp;&nbsp;|&nbsp;&nbsp; {{ $dayPaid->count() }} bills
                &nbsp;&nbsp;|&nbsp;&nbsp; Revenue: Rs {{ number_format($dayRevenue, 2) }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Bill No</th>
                        <th>Buyer</th>
                        <th>Time</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">GST</th>
                        <th class="text-right">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayBills as $bill)
                        <tr>
                            <td>{{ $bill->bill_no }}</td>
                            <td>{{ $bill->buyer_name ?: 'Cash Customer' }}</td>
                            <td>{{ $bill->created_at->format('h:i A') }}</td>
                            <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $bill->payment_mode) }}</td>
                            <td>
                                @if($bill->status === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-right">Rs {{ number_format($bill->subtotal, 2) }}</td>
                            <td class="text-right">Rs {{ number_format($bill->gst_amount, 2) }}</td>
                            <td class="text-right">Rs {{ number_format($bill->net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tr class="subtotal-row">
                    <td colspan="6">Day Total (paid bills)</td>
                    <td class="text-right">Rs {{ number_format($dayGst, 2) }}</td>
                    <td class="text-right">Rs {{ number_format($dayRevenue, 2) }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    {{-- Grand Total --}}
    <table style="margin-top:8px;">
        <tfoot>
            <tr>
                <td colspan="6">Grand Total ({{ $totalBills }} bills)</td>
                <td class="text-right">Rs {{ number_format($totalGst, 2) }}</td>
                <td class="text-right">Rs {{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Item-wise Summary --}}
    <div class="section-heading">Item-wise Summary</div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th class="text-right">Quantity Sold</th>
                <th class="text-right">Amount (pre-GST)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemSummary as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($row['qty'], 2), '0'), '.') }}</td>
                    <td class="text-right">Rs {{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No items sold in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Payment Mode Summary --}}
    <div class="section-heading">Payment Mode Summary</div>
    <table>
        <thead>
            <tr>
                <th>Payment Mode</th>
                <th class="text-right">Bills</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($modeSummary as $row)
                <tr>
                    <td>{{ $row['mode'] }}</td>
                    <td class="text-right">{{ $row['count'] }}</td>
                    <td class="text-right">Rs {{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No bills in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ $clubDetails->name }} &mdash; Confidential</div>

</body>
</html>
