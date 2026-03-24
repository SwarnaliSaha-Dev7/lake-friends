<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $session->session_no }}</title>
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
        .s-billed    { background: #ede9fe; color: #4f46e5; border: 1px solid #a5b4fc; }
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

        .badge {
            display: inline-block;
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 3px;
            vertical-align: middle;
        }
        .b-b1g1 { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .b-pct  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .b-flat { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }

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
        .bill-offer-lbl { text-align: right; color: #d97706; font-weight: 500; width: 65%; }
        .bill-offer-val { text-align: center; font-weight: 600; color: #6c757d; width: 35%; }

        .grand-total-row { background: #212529; border-radius: 4px; }
        .grand-total-row td { padding: 8px 10px !important; color: #fff !important; font-weight: bold; }
        .grand-total-row .bill-lbl { color: #fff !important; font-size: 12px; }
        .grand-total-row .bill-val { color: #fff !important; font-size: 14px; }
    </style>
</head>
<body>

    @php
        $logoPath   = public_path('assets/images/logo.svg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $statusPillClass = $session->status === 'billed' ? 's-billed' : 's-cancelled';
        $sessionDate     = \Carbon\Carbon::parse($session->created_at)->format('d/m/Y');
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
                <div style="font-size:16px; font-weight:bold; color:#97A0AC; line-height:1.2;">Lake Friends Club</div>
                <div style="font-size:10px; color:#6c757d; margin-top:2px;">Kolkata, West Bengal</div>
            </td>
            <td style="width:30%; text-align:right; vertical-align:middle;">
                <div style="font-size:22px; font-weight:bold; color:#97A0AC; letter-spacing:2px; text-transform:uppercase;">Invoice</div>
                <div style="font-size:10px; color:#6c757d; margin-top:2px;">{{ $session->session_no }}</div>
            </td>
        </tr>
    </table>

    <hr style="border:none; border-top:2px solid #97A0AC; margin-bottom:14px;">

    {{-- ===== Meta ===== --}}
    <table class="meta-table">
        <tr>
            <td class="meta-left">
                <div class="meta-label">Session No: <span class="meta-value">{{ $session->session_no }}</span></div>
                <div class="meta-label" style="margin-top:3px;">Bill No: <span class="meta-value">{{ $session->bill_no ?? '—' }}</span></div>
                <div style="margin-top:3px;">
                    <span class="meta-label">Date: <span class="meta-value">{{ $sessionDate }}</span></span>
                    &nbsp;&nbsp;
                    <span class="meta-label">Status: </span>
                    <span class="status-pill {{ $statusPillClass }}">{{ ucfirst($session->status) }}</span>
                </div>
            </td>
            <td class="meta-right">
                <div class="meta-heading">Billed to</div>
                <div class="meta-label" style="margin-top:2px;">{{ $session->member->name ?? '—' }}</div>
                <div class="meta-label" style="margin-top:2px;">{{ $session->member->email ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ===== Food Order Summary ===== --}}
    @if($foodItems->count())
        <div class="section-label">
            <span class="label-icon">F</span> Food Order Summary
        </div>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th class="tc">Quantity</th>
                    <th class="tr">Unit Price</th>
                    <th class="tr">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($foodItems as $item)
                    @php
                        $offer      = $item->offer_applied;
                        $offerBadge = '';
                        if ($offer) {
                            if (($offer['type_slug'] ?? '') === 'b1g1')
                                $offerBadge = '<span class="badge b-b1g1">B1G1</span>';
                            elseif (($offer['type_slug'] ?? '') === 'percentage' && ($offer['discount_value'] ?? 0))
                                $offerBadge = '<span class="badge b-pct">' . $offer['discount_value'] . '% off</span>';
                            elseif (($offer['type_slug'] ?? '') === 'flat' && ($offer['discount_value'] ?? 0))
                                $offerBadge = '<span class="badge b-flat">Rs ' . $offer['discount_value'] . ' off</span>';
                        }
                    @endphp
                    <tr>
                        <td>{!! ($item->foodItem->name ?? '—') . $offerBadge !!}</td>
                        <td class="tc">{{ $item->quantity }}</td>
                        <td class="tr">Rs {{ number_format($item->unit_price, 2) }}</td>
                        <td class="tr">Rs {{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== Liquor Order Summary ===== --}}
    @if($liquorItems->count())
        <div class="section-label">
            <span class="label-icon">L</span> Liquor Order Summary
        </div>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th class="tc">Volume</th>
                    <th class="tc">Quantity</th>
                    <th class="tr">Unit Price</th>
                    <th class="tr">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liquorItems as $item)
                    @php
                        $offer      = $item->offer_applied;
                        $offerBadge = '';
                        if ($offer) {
                            if (($offer['type_slug'] ?? '') === 'b1g1')
                                $offerBadge = '<span class="badge b-b1g1">B1G1</span>';
                            elseif (($offer['type_slug'] ?? '') === 'percentage' && ($offer['discount_value'] ?? 0))
                                $offerBadge = '<span class="badge b-pct">' . $offer['discount_value'] . '% off</span>';
                            elseif (($offer['type_slug'] ?? '') === 'flat' && ($offer['discount_value'] ?? 0))
                                $offerBadge = '<span class="badge b-flat">Rs ' . $offer['discount_value'] . ' off</span>';
                        }
                        $volume = $item->unit === 'btl' ? '1 BTL' : (($item->metadata['volume_ml'] ?? null) ? $item->metadata['volume_ml'] . ' ml' : '—');
                    @endphp
                    <tr>
                        <td>{!! ($item->foodItem->name ?? '—') . $offerBadge !!}</td>
                        <td class="tc">{{ $volume }}</td>
                        <td class="tc">{{ $item->quantity }}</td>
                        <td class="tr">Rs {{ number_format($item->unit_price, 2) }}</td>
                        <td class="tr">Rs {{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ===== Bill Summary ===== --}}
    <div class="bill-box">
        <table class="bill-row-table">
            <tr>
                <td class="bill-lbl">Subtotal</td>
                <td class="bill-val">Rs {{ number_format($session->taxable_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="bill-lbl">GST ({{ number_format($session->gst_percentage, 0) }}%)</td>
                <td class="bill-val">Rs {{ number_format($session->gst_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="bill-offer-lbl">Offer Applied</td>
                <td class="bill-offer-val">-Rs {{ number_format($session->discount_amount, 2) }}</td>
            </tr>
            <tr class="grand-total-row">
                <td class="bill-lbl">Grand Total</td>
                <td class="bill-val" style="font-size:14px;">Rs {{ number_format($session->net_amount, 2) }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
