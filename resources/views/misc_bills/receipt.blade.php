<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $bill->bill_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            background: #fff;
            padding: 28px 32px;
        }

        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { vertical-align: top; padding: 2px 0; }
        .meta-left  { width: 55%; }
        .meta-right { width: 45%; text-align: right; }

        .meta-label  { color: #6c757d; font-size: 11px; }
        .meta-value  { color: #212529; font-weight: bold; font-size: 11px; }
        .meta-heading { font-size: 12px; font-weight: bold; color: #212529; margin-bottom: 3px; }

        .status-pill {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }
        .s-paid      { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .s-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .divider { border: none; border-top: 1px solid #dee2e6; margin: 14px 0; }

        .section-label {
            font-size: 12px;
            font-weight: bold;
            color: #212529;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        .label-icon {
            display: inline-block;
            background: #e0f2fe;
            color: #0ea5e9;
            border-radius: 4px;
            padding: 2px 5px;
            margin-right: 5px;
            font-size: 10px;
            font-weight: bold;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 4px;
        }
        .order-table thead tr { background: #97A0AC; }
        .order-table thead th {
            color: #fff;
            font-weight: 500;
            font-size: 11px;
            padding: 7px 10px;
            text-align: left;
            white-space: nowrap;
        }
        .order-table thead th.tc { text-align: center; }
        .order-table thead th.tr { text-align: right; }

        .order-table tbody td {
            padding: 7px 10px;
            font-size: 11px;
            color: #6c757d;
            border-bottom: 1px solid #f1f1f1;
            white-space: nowrap;
        }
        .order-table tbody td.tc { text-align: center; }
        .order-table tbody td.tr { text-align: right; }
        .order-table tbody tr:last-child td { border-bottom: none; }

        .bill-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 16px;
        }
        .bill-row-table { width: 100%; border-collapse: collapse; }
        .bill-row-table tr td { padding: 5px 8px; font-size: 11px; }
        .bill-row-table tr { border-bottom: 1px solid #dee2e6; }
        .bill-row-table tr:last-child { border-bottom: none; }
        .bill-lbl { text-align: right; color: #6c757d; width: 65%; }
        .bill-val { text-align: center; font-weight: 600; color: #212529; width: 35%; }

        .grand-total-row { background: #212529; border-radius: 4px; }
        .grand-total-row td { padding: 8px 10px !important; color: #fff !important; font-weight: bold; }
        .grand-total-row .bill-lbl { color: #fff !important; font-size: 12px; }
        .grand-total-row .bill-val { color: #fff !important; font-size: 14px; }

        .words-box {
            margin-top: 10px;
            font-size: 11px;
            color: #212529;
            font-style: italic;
        }

        .simple-info-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 16px;
        }
        .simple-info-table { width: 100%; border-collapse: collapse; }
        .simple-info-table td { font-size: 11px; padding: 3px 0; }
        .simple-info-table td:first-child { color: #6c757d; width: 60%; }
        .simple-info-table td:last-child { color: #212529; text-align: right; font-weight: 600; width: 40%; }

        .cancelled-mark {
            position: fixed;
            top: 260px;
            left: 90px;
            font-size: 70px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.18);
            transform: rotate(-25deg);
            z-index: 10;
        }
    </style>
</head>
<body>

    @if($bill->status === 'cancelled')
        <div class="cancelled-mark">CANCELLED</div>
    @endif

    @php
        $logoBase64 = null;

        if (!empty($clubDetails?->logo)) {
            $logoPath = public_path($clubDetails->logo);

            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $statusPillClass = $bill->status === 'paid' ? 's-paid' : 's-cancelled';
        $billDate = \Carbon\Carbon::parse($bill->created_at)->format('d/m/Y');
    @endphp

    {{-- ===== Club header ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
        <tr>
            <td style="width:15%; vertical-align:middle; padding-right:12px;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width:60px; height:auto; display:block;">
                @endif
            </td>
            <td style="vertical-align:middle;">
                <div style="font-size:16px; font-weight:bold; color:#97A0AC; line-height:1.2;">
                    {{ $clubDetails->name ?? '' }}
                </div>
                <div style="font-size:10px; color:#6c757d; margin-top:2px;">{{ $clubDetails->address ?? '' }}</div>
            </td>
            <td style="width:30%; text-align:right; vertical-align:middle;">
                <div style="font-size:22px; font-weight:bold; color:#97A0AC; letter-spacing:2px; text-transform:uppercase;">Receipt</div>
                <div style="font-size:10px; color:#6c757d; margin-top:2px;">{{ $bill->bill_no }}</div>
            </td>
        </tr>
    </table>

    <hr style="border:none; border-top:2px solid #97A0AC; margin-bottom:14px;">

    {{-- ===== Meta ===== --}}
    <table class="meta-table">
        <tr>
            <td class="meta-left">
                <div class="meta-label">Bill No: <span class="meta-value">{{ $bill->bill_no }}</span></div>
                <div class="meta-label" style="margin-top:3px;">Receipt No: <span class="meta-value">{{ $bill->mr_no }}</span></div>
                <div style="margin-top:3px;">
                    <span class="meta-label">Date: <span class="meta-value">{{ $billDate }}</span></span>
                    &nbsp;&nbsp;
                    <span class="meta-label">Status: </span>
                    <span class="status-pill {{ $statusPillClass }}">{{ ucfirst($bill->status) }}</span>
                </div>
            </td>
            <td class="meta-right">
                <div class="meta-heading">Received From</div>
                <div class="meta-label" style="margin-top:2px;">{{ $receipt['received_from'] }}</div>
                @if($receipt['buyer_contact'])
                <div class="meta-label" style="margin-top:2px;">{{ $receipt['buyer_contact'] }}</div>
                @endif
                <div class="meta-label" style="margin-top:2px;">Payment: <span class="meta-value">{{ $receipt['payment_mode'] }}</span></div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ===== Items ===== --}}
    <div class="section-label">
        <span class="label-icon">M</span> Item Details
    </div>
    <table class="order-table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th class="tc">Qty</th>
                <th class="tc">Unit</th>
                <th class="tr">Rate</th>
                <th class="tr">Taxable</th>
                <th class="tc">GST %</th>
                <th class="tr">GST Amt</th>
                <th class="tr">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
                <tr>
                    <td>{{ $item->miscItem->name ?? '—' }}</td>
                    <td class="tc">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="tc">{{ $item->unit ?? '—' }}</td>
                    <td class="tr">Rs {{ number_format($item->unit_price, 2) }}</td>
                    <td class="tr">Rs {{ number_format($item->total_amount, 2) }}</td>
                    <td class="tc">{{ number_format($item->gst_percentage, 2) }}%</td>
                    <td class="tr">Rs {{ number_format($item->gst_amount, 2) }}</td>
                    <td class="tr">Rs {{ number_format($item->total_amount + $item->gst_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($bill->remarks)
    <div class="simple-info-box">
        <div class="meta-heading" style="margin-bottom:4px;">Remarks</div>
        <div class="meta-label">{{ $bill->remarks }}</div>
    </div>
    @endif

    {{-- ===== Bill Summary ===== --}}
    <div class="bill-box">
        <table class="bill-row-table">
            <tr>
                <td class="bill-lbl">Taxable Amount</td>
                <td class="bill-val">Rs {{ $receipt['taxable_amount'] }}</td>
            </tr>
            <tr>
                <td class="bill-lbl">Misc GST</td>
                <td class="bill-val">Rs {{ $receipt['misc_gst'] }}</td>
            </tr>
            <tr class="grand-total-row">
                <td class="bill-lbl">Net Amount</td>
                <td class="bill-val" style="font-size:14px;">Rs {{ $receipt['net_amount'] }}</td>
            </tr>
        </table>
    </div>

    <div class="words-box">{{ $receipt['amount_words'] }}</div>

    <div class="simple-info-box">
        <table class="simple-info-table">
            <tr>
                <td>Collected By</td>
                <td>{{ $receipt['collected_by'] }}</td>
            </tr>
            <tr>
                <td>Printed At</td>
                <td>{{ $receipt['printed_at'] }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
