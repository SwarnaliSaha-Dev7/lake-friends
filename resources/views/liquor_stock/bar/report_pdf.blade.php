<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2d2d2d; }

        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #7367f0; padding-bottom: 10px; }
        .header h1 { font-size: 18px; color: #7367f0; font-weight: bold; }
        .header p  { font-size: 11px; color: #666; margin-top: 3px; }

        .summary-cards { display: table; width: 100%; margin-bottom: 16px; border-spacing: 6px; }
        .card { display: table-cell; width: 25%; padding: 10px 14px; border-radius: 6px; color: #fff; }
        .card .card-label { font-size: 9px; opacity: 0.85; margin-bottom: 4px; }
        .card .card-value { font-size: 16px; font-weight: bold; }
        .card .card-sub   { font-size: 8px; opacity: 0.75; margin-top: 2px; }
        .card-blue   { background: #1e9de8; }
        .card-green  { background: #28c76f; }
        .card-orange { background: #ff9f43; }
        .card-purple { background: #7367f0; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead tr { background: #7367f0; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; white-space: nowrap; }
        tbody tr:nth-child(even) { background: #f5f4ff; }
        tbody td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #e8e8e8; }
        tfoot tr { background: #ede9ff; font-weight: bold; }
        tfoot td { padding: 7px 8px; font-size: 10px; border-top: 2px solid #7367f0; }

        .text-success { color: #28a745; font-weight: bold; }
        .text-danger  { color: #dc3545; font-weight: bold; }
        .badge-beer    { background: #ffc107; color: #212529; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-spirit  { background: #17a2b8; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-danger  { background: #dc3545; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-warning { background: #ffc107; color: #212529; padding: 1px 5px; border-radius: 3px; font-size: 8px; }

        .footer { margin-top: 14px; text-align: right; font-size: 9px; color: #999; }
        .note   { font-size: 8px; color: #888; margin-bottom: 6px; }
    </style>
</head>
<body>

    <div class="header">
        <p style="margin:0 0 5px;"><img src="{{ public_path($clubDetails->logo) }}" alt="Club Logo" style="max-width:55px; max-height:55px;"></p>
        <p style="margin:0 0 2px; font-size:14px; font-weight:700; color:#1e293b; text-align:center;">{{ $clubDetails->name }}</p>
        <p style="margin:0 0 12px; font-size:9px; color:#64748b; text-align:center;">{{ $clubDetails->address }}</p>
        <h1>Bar Stock Report</h1>
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
                <div class="card-value">{{ number_format($totalOpening, 2) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalOpeningAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-green">
                <div class="card-label">IN from Godown</div>
                <div class="card-value">+{{ number_format($totalIn, 2) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalInAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-orange">
                <div class="card-label">OUT (Sales)</div>
                <div class="card-value">-{{ number_format($totalOut, 2) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalOutAmount, 0) }}</div>
            </td>
            <td width="6"></td>
            <td class="card card-purple">
                <div class="card-label">Closing Stock</div>
                <div class="card-value">{{ number_format($totalClosing, 2) }} BTL</div>
                <div class="card-label" style="margin-top:3px;">Rs {{ number_format($totalClosingAmount, 0) }}</div>
            </td>
        </tr>
    </table>

    <div class="note">* Spirits shown in ml | Beer shown in BTL | Summary totals in bottle equivalents (ml / size)</div>

    {{-- Report Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Type</th>
                <th>Size</th>
                <th>Opening</th>
                <th>Opening Value</th>
                <th>IN (+)</th>
                <th>IN Value</th>
                <th>OUT (-)</th>
                <th>OUT Value</th>
                <th>Closing</th>
                <th>Closing Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $index => $row)
                @php
                    $alertQty     = (int) ($row['item']->low_stock_alert_qty ?? 0);
                    $isBeer       = $row['is_beer'];
                    $sizeMl       = $row['size_ml'];
                    $unit         = $row['unit'];
                    $closing      = $row['closing_qty'];
                    $closingBtlEq = $isBeer ? $closing : ($sizeMl > 0 ? floor($closing / $sizeMl) : 0);
                    $isOut        = $closing === 0 && $row['opening_qty'] > 0;
                    $isLow        = !$isOut && $alertQty > 0 && $closingBtlEq <= $alertQty;

                    $fmtQty = function($qty, $prefix = '') use ($isBeer, $sizeMl, $unit) {
                        if ($qty <= 0) return '—';
                        if ($isBeer) return $prefix . number_format($qty) . ' BTL';
                        $btl = $sizeMl > 0 ? (int) floor($qty / $sizeMl) : 0;
                        $rem = $sizeMl > 0 ? ($qty % $sizeMl) : $qty;
                        $breakdown = $btl > 0
                            ? ' (' . $btl . ' BTL' . ($rem > 0 ? ' ' . number_format($rem) . ' ml' : '') . ')'
                            : '';
                        return $prefix . number_format($qty) . ' ml' . $breakdown;
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['item']->name }}</td>
                    <td>{{ $row['item']->foodItemCat->name ?? '—' }}</td>
                    <td>
                        @if($isBeer) <span class="badge-beer">Beer</span>
                        @else <span class="badge-spirit">Spirit</span>
                        @endif
                    </td>
                    <td>{{ $sizeMl ? $sizeMl . ' ml' : '—' }}</td>
                    <td>{{ $fmtQty($row['opening_qty']) }}</td>
                    <td>{{ $row['opening_amount'] > 0 ? 'Rs '.number_format($row['opening_amount'], 2) : '—' }}</td>
                    <td class="text-success">{{ $fmtQty($row['in_qty'], '+') }}</td>
                    <td class="text-success">{{ $row['in_amount'] > 0 ? 'Rs '.number_format($row['in_amount'], 2) : '—' }}</td>
                    <td class="text-danger">{{ $fmtQty($row['out_qty'], '-') }}</td>
                    <td class="text-danger">{{ $row['out_amount'] > 0 ? 'Rs '.number_format($row['out_amount'], 2) : '—' }}</td>
                    <td>
                        {{ $fmtQty($closing) }}
                        @if($isOut) <span class="badge-danger">Empty</span>
                        @elseif($isLow) <span class="badge-warning">Low</span>
                        @endif
                    </td>
                    <td style="color:#5e50ee; font-weight:bold;">{{ $row['closing_amount'] > 0 ? 'Rs '.number_format($row['closing_amount'], 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right; padding-right:10px;">Total</td>
                <td>—</td>
                <td>Rs {{ number_format($totalOpeningAmount, 2) }}</td>
                <td>—</td>
                <td class="text-success">Rs {{ number_format($totalInAmount, 2) }}</td>
                <td>—</td>
                <td class="text-danger">Rs {{ number_format($totalOutAmount, 2) }}</td>
                <td>—</td>
                <td style="color:#5e50ee;">Rs {{ number_format($totalClosingAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Bar Stock Report &mdash; {{ $clubDetails->name }}</div>

</body>
</html>
