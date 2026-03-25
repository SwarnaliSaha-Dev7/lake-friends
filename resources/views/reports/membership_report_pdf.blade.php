<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

    .header { background: #1e3a5f; color: #fff; padding: 16px 24px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .header p { font-size: 10px; color: rgba(255,255,255,0.75); }

    .meta-item .label { font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
    .meta-item .value { font-size: 11px; font-weight: 600; color: #1e293b; }

    .stats-table { width: calc(100% - 48px); margin: 0 24px 14px; border-collapse: separate; border-spacing: 6px 0; }
    .stat-card { border-radius: 6px; padding: 10px 8px; text-align: center; }
    .stat-card .s-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
    .stat-card .s-val { font-size: 14px; font-weight: 700; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; margin: 0 0 0 0; font-size: 9px; }
    thead th {
        background: #1e3a5f; color: #fff; padding: 7px 10px;
        text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em;
    }
    tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:last-child td { border-bottom: none; }

    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 600; }
    .badge-success { background: #dcfce7; color: #16a34a; }
    .badge-warning { background: #fef3c7; color: #d97706; }
    .badge-danger  { background: #fee2e2; color: #dc2626; }
    .badge-blue    { background: #dbeafe; color: #2563eb; }

    .text-danger { color: #dc2626; font-weight: 600; }
    .text-muted  { color: #94a3b8; }

    .footer { margin-top: 12px; padding: 8px 24px; border-top: 1px solid #e2e8f0; }
    .footer .f-right { float: right; }
    .footer small { font-size: 8px; color: #94a3b8; }

    .table-wrap { padding: 0 24px; }
    .no-data { text-align: center; padding: 40px; color: #94a3b8; font-size: 11px; }
</style>
</head>
<body>

<div class="header" style="text-align:center;">
    <p style="margin:0 0 5px;"><img src="{{ public_path($clubDetails->logo) }}" alt="Club Logo" style="max-width:50px; max-height:50px;"></p>
    <p style="margin:0 0 2px; font-size:13px; font-weight:700; color:#fff; text-align:center;">{{ $clubDetails->name }}</p>
    <p style="margin:0 0 10px; font-size:8px; color:rgba(255,255,255,0.7); text-align:center;">{{ $clubDetails->address }}</p>
    <h1>{{ $tab_label }}</h1>
    <p>Generated on {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
       Period: {{ $from }} – {{ $to }} &nbsp;|&nbsp;
       Type: {{ $member_type === 'all' ? 'All (Club + Swimming)' : ucwords($member_type) . ' Membership' }}
    </p>
</div>

@php
    $total = count($data);
@endphp

{{-- Stats --}}
@if($report_type === 'memberships')
@php
    $newCount     = collect($data)->where('is_renewal', false)->count();
    $renewalCount = collect($data)->where('is_renewal', true)->count();
    $totalFee     = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['fee']));
    $totalNet     = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['net_amount']));
@endphp
<table class="stats-table">
    <tr>
        <td class="stat-card" style="background:#eff6ff;"><div class="s-label">Total</div><div class="s-val" style="color:#2563eb;">{{ $total }}</div></td>
        <td class="stat-card" style="background:#f0fdf4;"><div class="s-label">New</div><div class="s-val" style="color:#16a34a;">{{ $newCount }}</div></td>
        <td class="stat-card" style="background:#fffbeb;"><div class="s-label">Renewal</div><div class="s-val" style="color:#d97706;">{{ $renewalCount }}</div></td>
        <td class="stat-card" style="background:#fdf4ff;"><div class="s-label">Total Fee</div><div class="s-val" style="color:#7c3aed;">₹{{ number_format($totalFee, 2) }}</div></td>
        <td class="stat-card" style="background:#f0fdf4;"><div class="s-label">Net Collected</div><div class="s-val" style="color:#16a34a;">₹{{ number_format($totalNet, 2) }}</div></td>
    </tr>
</table>
@elseif($report_type === 'expiry_fines')
@php
    $expired      = collect($data)->where('expiry_status', 'Expired')->count();
    $expiringSoon = collect($data)->where('expiry_status', '!=', 'Expired')->count();
    $totalFine    = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['pending_fine']));
@endphp
<table class="stats-table">
    <tr>
        <td class="stat-card" style="background:#eff6ff;"><div class="s-label">Total</div><div class="s-val" style="color:#2563eb;">{{ $total }}</div></td>
        <td class="stat-card" style="background:#fef2f2;"><div class="s-label">Expired</div><div class="s-val" style="color:#dc2626;">{{ $expired }}</div></td>
        <td class="stat-card" style="background:#fffbeb;"><div class="s-label">Expiring Soon</div><div class="s-val" style="color:#d97706;">{{ $expiringSoon }}</div></td>
        <td class="stat-card" style="background:#fef2f2;"><div class="s-label">Total Fines</div><div class="s-val" style="color:#dc2626;">₹{{ number_format($totalFine, 2) }}</div></td>
    </tr>
</table>
@elseif($report_type === 'renewals')
@php
    $totalFeeR  = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['fee']));
    $totalFineR = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['fine_at_renewal']));
    $totalNetR  = collect($data)->sum(fn($r) => (float) str_replace(',', '', $r['net_amount']));
@endphp
<table class="stats-table">
    <tr>
        <td class="stat-card" style="background:#eff6ff;"><div class="s-label">Renewals</div><div class="s-val" style="color:#2563eb;">{{ $total }}</div></td>
        <td class="stat-card" style="background:#f0fdf4;"><div class="s-label">Total Fee</div><div class="s-val" style="color:#16a34a;">₹{{ number_format($totalFeeR, 2) }}</div></td>
        <td class="stat-card" style="background:#fef2f2;"><div class="s-label">Total Fine</div><div class="s-val" style="color:#dc2626;">₹{{ number_format($totalFineR, 2) }}</div></td>
        <td class="stat-card" style="background:#fdf4ff;"><div class="s-label">Net Collected</div><div class="s-val" style="color:#7c3aed;">₹{{ number_format($totalNetR, 2) }}</div></td>
    </tr>
</table>
@endif

<div class="table-wrap">
@if(empty($data))
    <div class="no-data">No records found for the selected period.</div>
@else

@if($report_type === 'memberships')
<table>
    <thead>
        <tr>
            <th>#</th><th>Name</th><th>Card No</th><th>Type</th><th>Plan</th>
            <th>Start Date</th><th>Expiry Date</th><th>Fee</th><th>Net Amount</th><th>Status</th><th>Tag</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $r)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['card_no'] }}</td>
            <td>{{ $r['member_type'] }}</td>
            <td>{{ $r['plan'] }}</td>
            <td>{{ $r['start_date'] }}</td>
            <td>{{ $r['expiry_date'] }}</td>
            <td>₹{{ $r['fee'] }}</td>
            <td>₹{{ $r['net_amount'] }}</td>
            <td>{{ $r['status'] }}</td>
            <td><span class="badge {{ $r['is_renewal'] ? 'badge-warning' : 'badge-success' }}">{{ $r['is_renewal'] ? 'Renewal' : 'New' }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

@elseif($report_type === 'expiry_fines')
<table>
    <thead>
        <tr>
            <th>#</th><th>Name</th><th>Card No</th><th>Type</th><th>Plan</th>
            <th>Expiry Date</th><th>Days Overdue</th><th>Pending Fine</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $r)
        @php $fineVal = (float) str_replace(',', '', $r['pending_fine']); @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['card_no'] }}</td>
            <td>{{ $r['member_type'] }}</td>
            <td>{{ $r['plan'] }}</td>
            <td>{{ $r['expiry_date'] }}</td>
            <td>{{ $r['days_overdue'] > 0 ? $r['days_overdue'].' days' : '—' }}</td>
            <td class="{{ $fineVal > 0 ? 'text-danger' : '' }}">₹{{ $r['pending_fine'] }}</td>
            <td><span class="badge {{ $r['expiry_status'] === 'Expired' ? 'badge-danger' : 'badge-warning' }}">{{ $r['expiry_status'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

@elseif($report_type === 'renewals')
<table>
    <thead>
        <tr>
            <th>#</th><th>Name</th><th>Card No</th><th>Type</th><th>Plan</th>
            <th>Renewal Date</th><th>New Expiry</th><th>Fee</th><th>Fine at Renewal</th><th>Net Amount</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $r)
        @php $fineVal = (float) str_replace(',', '', $r['fine_at_renewal']); @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['card_no'] }}</td>
            <td>{{ $r['member_type'] }}</td>
            <td>{{ $r['plan'] }}</td>
            <td>{{ $r['renewal_date'] }}</td>
            <td>{{ $r['expiry_date'] }}</td>
            <td>₹{{ $r['fee'] }}</td>
            <td class="{{ $fineVal > 0 ? 'text-danger' : 'text-muted' }}">₹{{ $r['fine_at_renewal'] }}</td>
            <td>₹{{ $r['net_amount'] }}</td>
            <td>{{ $r['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@endif
</div>

<div class="footer">
    <small class="f-right">Period: {{ $from }} – {{ $to }}</small>
    <small>{{ $clubDetails->name }} &nbsp;|&nbsp; {{ $tab_label }}</small>
</div>

</body>
</html>
