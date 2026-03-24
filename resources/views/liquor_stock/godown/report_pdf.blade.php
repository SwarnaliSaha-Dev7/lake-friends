<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2d2d2d; }

        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1e9de8; padding-bottom: 10px; }
        .header h1 { font-size: 18px; color: #1e9de8; font-weight: bold; }
        .header p  { font-size: 11px; color: #666; margin-top: 3px; }

        .summary-cards { display: table; width: 100%; margin-bottom: 16px; border-spacing: 6px; }
        .card { display: table-cell; width: 25%; padding: 10px 14px; border-radius: 6px; color: #fff; }
        .card .card-label { font-size: 9px; opacity: 0.85; margin-bottom: 4px; }
        .card .card-value { font-size: 16px; font-weight: bold; }
        .card-blue   { background: #1e9de8; }
        .card-green  { background: #28c76f; }
        .card-orange { background: #ff9f43; }
        .card-red    { background: #ea5455; }
        .card-purple { background: #7367f0; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead tr { background: #1e9de8; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; white-space: nowrap; }
        tbody tr:nth-child(even) { background: #f5f8ff; }
        tbody td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #e8e8e8; }
        tfoot tr { background: #e8f0fe; font-weight: bold; }
        tfoot td { padding: 7px 8px; font-size: 10px; border-top: 2px solid #1e9de8; }

        .text-success { color: #28a745; font-weight: bold; }
        .text-danger  { color: #dc3545; font-weight: bold; }
        .text-muted   { color: #888; }
        .badge-danger  { background: #dc3545; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-warning { background: #ffc107; color: #212529; padding: 1px 5px; border-radius: 3px; font-size: 8px; }

        .footer { margin-top: 14px; text-align: right; font-size: 9px; color: #999; }
    </style>
</head>
<body>

        <div class="modal-header d-flex gap-3 justify-content-between align-items-center border-0">
            <img src="{{ public_path($clubDetails->logo) }}" alt="img" loading="lazy" fetchpriority="auto" style="max-width: 50px;">
                <p class="m-0 lh-2">{{ $clubDetails->name }}</p>
                <p class="m-0 lh-2">{{ $clubDetails->address }}</p>
        </div>

    <div class="header">
        <h1>Godown Stock Report</h1>
        <p>
            Period:
            @if($from->toDateString() === $to->toDateString())
                {{ $from->format('d M Y') }}
            @else
                {{ $from->format('d M Y') }} &mdash; {{ $to->format('d M Y') }}
            @endif
            &nbsp;&nbsp;|&nbsp;&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>

    {{-- Summary Cards --}}
    <table class="summary-cards">
        <tr>
            <td class="card card-blue">
                <div class="card-label">Opening Stock</div>
                <div class="card-value">{{ number_format($totalOpening) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalOpeningAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-green">
                <div class="card-label">Total IN (Period)</div>
                <div class="card-value">+{{ number_format($totalIn) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalInAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-orange">
                <div class="card-label">Total OUT (Period)</div>
                <div class="card-value">-{{ number_format($totalOut) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalOutAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-red">
                <div class="card-label">Transferred to Bar</div>
                <div class="card-value">-{{ number_format($totalTransfer) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalTransferAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-purple">
                <div class="card-label">Closing Stock</div>
                <div class="card-value">{{ number_format($totalClosing) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalClosingAmount, 0) }}</div>
            </td>
        </tr>
    </table>

    {{-- Report Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Size</th>
                <th>Opening BTL</th>
                <th>Opening Value</th>
                <th>IN (+)</th>
                <th>IN Value</th>
                <th>OUT (-)</th>
                <th>OUT Value</th>
                <th>To Bar (-)</th>
                <th>Transfer Value</th>
                <th>Closing BTL</th>
                <th>Closing Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $index => $row)
                @php
                    $alertQty = (int) ($row['item']->low_stock_alert_qty ?? 0);
                    $isOut    = $row['closing_qty'] === 0;
                    $isLow    = !$isOut && $alertQty > 0 && $row['closing_qty'] <= $alertQty;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['item']->name }}</td>
                    <td>{{ $row['item']->foodItemCat->name ?? '—' }}</td>
                    <td>{{ $row['item']->size_ml ? $row['item']->size_ml.' ml' : '—' }}</td>
                    <td>{{ $row['opening_qty'] }} BTL</td>
                    <td>{{ $row['opening_amount'] > 0 ? 'Rs '.number_format($row['opening_amount'], 2) : '—' }}</td>
                    <td class="text-success">{{ $row['in_qty'] > 0 ? '+'.$row['in_qty'] : '—' }}</td>
                    <td class="text-success">{{ $row['in_amount'] > 0 ? 'Rs '.number_format($row['in_amount'], 2) : '—' }}</td>
                    <td class="text-danger">{{ $row['out_qty'] > 0 ? '-'.$row['out_qty'] : '—' }}</td>
                    <td class="text-danger">{{ $row['out_amount'] > 0 ? 'Rs '.number_format($row['out_amount'], 2) : '—' }}</td>
                    <td style="color:#ea5455; font-weight:bold;">{{ $row['transfer_qty'] > 0 ? '-'.$row['transfer_qty'] : '—' }}</td>
                    <td style="color:#ea5455;">{{ $row['transfer_amount'] > 0 ? 'Rs '.number_format($row['transfer_amount'], 2) : '—' }}</td>
                    <td>
                        {{ $row['closing_qty'] }} BTL
                        @if($isOut) <span class="badge-danger">Out</span>
                        @elseif($isLow) <span class="badge-warning">Low</span>
                        @endif
                    </td>
                    <td style="color:#0d6efd; font-weight:bold;">{{ $row['closing_amount'] > 0 ? 'Rs '.number_format($row['closing_amount'], 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right; padding-right:10px;">Total</td>
                <td>{{ $totalOpening }} BTL</td>
                <td>Rs {{ number_format($totalOpeningAmount, 2) }}</td>
                <td class="text-success">+{{ $totalIn }}</td>
                <td class="text-success">Rs {{ number_format($totalInAmount, 2) }}</td>
                <td class="text-danger">-{{ $totalOut }}</td>
                <td class="text-danger">Rs {{ number_format($totalOutAmount, 2) }}</td>
                <td style="color:#ea5455;">-{{ $totalTransfer }}</td>
                <td style="color:#ea5455;">Rs {{ number_format($totalTransferAmount, 2) }}</td>
                <td>{{ $totalClosing }} BTL</td>
                <td style="color:#0d6efd;">Rs {{ number_format($totalClosingAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Godown Stock Report &mdash; {{ $clubDetails->name }}</div>

</body>
</html>
