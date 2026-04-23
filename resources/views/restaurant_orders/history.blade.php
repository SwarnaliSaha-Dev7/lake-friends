@extends('base.app')

@section('title', 'Order History')
@section('page_title', 'Order History')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Order History</h2>
                </div>

                {{-- Stat cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#29b6f6,#0288d1);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Bills</div>
                                <div class="fs-4 fw-bold">{{ $totalOrders }}</div>
                            </div>
                            <i class="fa-solid fa-receipt fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#66bb6a,#388e3c);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Revenue</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($totalRevenue, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#ffa726,#e65100);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Discount</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($totalDiscount, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-tag fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#ab47bc,#7b1fa2);">
                            <div>
                                <div class="small opacity-75 mb-1">Avg Bill Value</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($avgOrder, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-chart-line fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>

                {{-- Date filter --}}
                <form method="GET" action="{{ route('restaurant-orders.history') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">From</label>
                        <input type="date" name="start_date" class="form-control form-control-sm shadow-none" value="{{ $startDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">To</label>
                        <input type="date" name="end_date" class="form-control form-control-sm shadow-none" value="{{ $endDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['start_date','end_date']))
                            <a href="{{ route('restaurant-orders.history') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fa-solid fa-xmark me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                    <div class="col-sm-auto ms-sm-auto">
                        <a href="{{ route('restaurant-orders.report.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-sm btn-outline-danger fw-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                        </a>
                    </div>
                </form>

                {{-- ── Session Orders ── --}}
                <div class="table-responsive mb-4">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Session No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Member</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Rounds</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Grand Total</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                @php
                                    $roundCount  = $session->orders->whereNotIn('status', ['cancelled'])->count();
                                    $statusClass = match($session->status) {
                                        'billed'    => 'bg-success-subtle text-success border-success',
                                        'cancelled' => 'bg-danger-subtle text-danger border-danger',
                                        'open'      => 'bg-warning-subtle text-warning border-warning',
                                        default     => 'bg-secondary-subtle text-secondary border-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $session->session_no }}</td>
                                    <td class="text-nowrap">
                                        <div class="fw-medium">{{ $session->member->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $session->member->email ?? '—' }}</small>
                                    </td>
                                    <td class="text-nowrap text-center">{{ $roundCount }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($session->created_at)->format('d-m-Y') }}</td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($session->net_amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $statusClass }}">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn view-session-btn"
                                            data-id="{{ $session->id }}" title="View session">
                                            <small><i class="fa-regular fa-eye"></i></small>
                                        </button>
                                        @if($session->status === 'billed')
                                            <a href="{{ route('order-sessions.invoice', $session->id) }}"
                                                class="border-0 bg-light p-1 rounded-3 lh-1 action-btn ms-1 d-inline-flex align-items-center"
                                                title="Download Invoice" target="_blank">
                                                <small><i class="fa-solid fa-download"></i></small>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No session orders in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    {{-- Session View Modal --}}
    <div class="modal fade" id="viewSessionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-semibold">Order Details</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="viewSessionModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('customJS')
<script>
$(document).ready(function () {

    /* ── View Session ── */
    $(document).on('click', '.view-session-btn', function () {
        var id = $(this).data('id');
        $('#viewSessionModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
        $('#viewSessionModal').modal('show');

        $.get('{{ route("order-sessions.show", ":id") }}'.replace(':id', id), function (res) {
            if (res.statusCode !== 200) {
                $('#viewSessionModalBody').html('<p class="text-danger text-center py-4">Failed to load session.</p>');
                return;
            }
            renderSessionModal(res.data, res.pending_total, res.wallet_balance, res.card_balance_info, res.minimum_usage_info);
        }).fail(function () {
            $('#viewSessionModalBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

    /* ── Session modal renderer ── */
    function renderSessionModal(session, pendingTotal, walletBalance, cardBalanceInfo, minimumUsageInfo) {
        function amount(value) {
            var num = parseFloat(value);
            return isNaN(num) ? '0.00' : num.toFixed(2);
        }

        var statusColor = { open: 'text-warning', billed: 'text-success', cancelled: 'text-danger' };
        var sc = statusColor[session.status] || 'text-muted';

        var html = '<div class="mb-3 pb-2 border-bottom d-flex justify-content-between align-items-start">'
            + '<div><div class="fw-bold fs-6">' + session.session_no + '</div>'
            + '<div class="text-muted small">' + (session.member ? session.member.name : '—') + '</div></div>'
            + '<span class="fw-semibold ' + sc + '">' + session.status.charAt(0).toUpperCase() + session.status.slice(1) + '</span>'
            + '</div>';

        var orders = session.orders || [];
        var roundNum = 0;
        orders.forEach(function (order) {
            var isCancelled = order.status === 'cancelled';
            if (!isCancelled) roundNum++;
            var stClass = { paid: 'text-success', pending: 'text-warning', cancelled: 'text-danger' }[order.status] || 'text-muted';
            var headerBg = isCancelled ? 'background:#fff5f5;' : 'background:#f8f9fa;';
            var rowStyle  = isCancelled ? ' style="opacity:0.6;text-decoration:line-through;"' : '';
            var itemRows = '';
            (order.items || []).forEach(function (it) {
                var meta = it.metadata || {};
                var name = (meta.is_cocktail && meta.cocktail_name)
                    ? meta.cocktail_name
                    : (it.food_item ? it.food_item.name : '—');
                var vol  = it.unit === 'btl' ? '1 BTL' : (meta.volume_ml ? meta.volume_ml + 'ml' : '');
                var offerBadge = '';
                if (it.offer_applied && !isCancelled) {
                    var of = it.offer_applied;
                    if (of.type_slug === 'b1g1') offerBadge = ' <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2" style="font-size:0.65rem;">B1G1</span>';
                    else if (of.type_slug === 'percentage' && of.discount_value) offerBadge = ' <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2" style="font-size:0.65rem;">' + of.discount_value + '% off</span>';
                    else if (of.type_slug === 'flat' && of.discount_value) offerBadge = ' <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2" style="font-size:0.65rem;">Rs ' + of.discount_value + ' off</span>';
                }
                itemRows += '<tr' + rowStyle + '><td class="text-muted small">' + name + offerBadge + '</td>'
                    + '<td class="text-center text-muted small">' + (vol || it.unit) + '</td>'
                    + '<td class="text-center text-muted small">' + it.quantity + '</td>'
                    + '<td class="text-end text-muted small">Rs ' + parseFloat(it.total_amount).toFixed(2) + '</td></tr>';
            });
            var roundLabel = isCancelled ? ('Cancelled — ' + order.order_no) : ('Round ' + roundNum + ' — ' + session.session_no);
            html += '<div class="mb-3 border rounded-3 overflow-hidden">'
                + '<div class="px-3 py-2 d-flex justify-content-between align-items-center" style="' + headerBg + '">'
                + '<span class="fw-semibold small">' + roundLabel + '</span>'
                + '<span class="small fw-medium ' + stClass + '">' + order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span>'
                + '</div>'
                + '<table class="table table-sm mb-0"><thead><tr>'
                + '<th style="font-size:0.75rem;">Item</th><th class="text-center" style="font-size:0.75rem;">Vol</th>'
                + '<th class="text-center" style="font-size:0.75rem;">Qty</th><th class="text-end" style="font-size:0.75rem;">Total</th>'
                + '</tr></thead><tbody>' + itemRows + '</tbody></table>'
                + (!isCancelled ? '<div class="px-3 py-2 text-end border-top small fw-semibold text-muted">Round Total: Rs '
                + parseFloat(parseFloat(order.taxable_amount) - parseFloat(order.discount_amount)).toFixed(2) + '</div>' : '')
                + '</div>';
        });

        if (!orders.length) html += '<p class="text-muted text-center py-3">No orders in this session.</p>';

        var sessionSubtotal = 0, sessionDiscount = 0, sessionGst = 0;
        orders.forEach(function (o) {
            if (o.status === 'cancelled') return;
            sessionSubtotal  += parseFloat(o.taxable_amount)  || 0;
            sessionDiscount  += parseFloat(o.discount_amount) || 0;
            sessionGst       += parseFloat(o.gst_amount)      || 0;
        });
        var sessionNet = sessionSubtotal - sessionDiscount + sessionGst;

        html += '<div class="p-3 bg-light border rounded-3 mt-2">'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">Subtotal</div><div class="col-4 text-center fw-semibold small">Rs ' + sessionSubtotal.toFixed(2) + '</div></div>'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">GST (10%)</div><div class="col-4 text-center fw-semibold small">Rs ' + sessionGst.toFixed(2) + '</div></div>'
            + (sessionDiscount > 0 ? '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-warning small fw-medium">Offer Applied</div><div class="col-4 text-center fw-semibold small text-muted">-Rs ' + sessionDiscount.toFixed(2) + '</div></div>' : '')
            + '<div class="row py-1 bg-dark text-white rounded-3 mx-0 mt-1"><div class="col-8 text-end small">Grand Total</div><div class="col-4 text-center fw-bold">Rs ' + sessionNet.toFixed(2) + '</div></div>'
            + '<div class="row mt-2"><div class="col-8 text-end text-muted small">Wallet Balance</div><div class="col-4 text-center fw-semibold text-success small">Rs ' + amount(walletBalance) + '</div></div>'
            + '</div>';

        if (session.status === 'billed') {
            cardBalanceInfo = cardBalanceInfo || {};
            minimumUsageInfo = minimumUsageInfo || {};

            html += '<div class="p-3 bg-white border rounded-3 mt-2">'
                + '<div class="fw-semibold mb-2">Card Balance</div>'
                + '<div class="row mb-1"><div class="col-8 text-muted small">Opening Balance</div><div class="col-4 text-end fw-semibold small">' + amount(cardBalanceInfo.opening_balance) + '</div></div>'
                + ((parseFloat(cardBalanceInfo.last_topup) || 0) > 0
                    ? '<div class="row mb-1"><div class="col-8 text-muted small">Last Topup</div><div class="col-4 text-end fw-semibold small">' + amount(cardBalanceInfo.last_topup) + '</div></div>'
                      + '<div class="row mb-1"><div class="col-8 text-muted small">(Opening + Topup)</div><div class="col-4 text-end fw-semibold small">' + amount(cardBalanceInfo.opening_plus_topup) + '</div></div>'
                    : '')
                + '<div class="row mb-1"><div class="col-8 text-muted small">Billed Amount</div><div class="col-4 text-end fw-semibold small">' + amount(cardBalanceInfo.billed_amount) + '</div></div>'
                + '<div class="row"><div class="col-8 text-muted small">Closing Balance</div><div class="col-4 text-end fw-semibold small">' + amount(cardBalanceInfo.closing_balance) + '</div></div>'
                + '</div>';

            html += '<div class="p-3 bg-white border rounded-3 mt-2">'
                + '<div class="fw-semibold mb-2">Minimum Usage Info</div>'
                + (minimumUsageInfo.applicable
                    ? '<div class="row mb-1"><div class="col-8 text-muted small">Minimum Charges</div><div class="col-4 text-end fw-semibold small">' + amount(minimumUsageInfo.minimum_charges) + '</div></div>'
                      + '<div class="row mb-1"><div class="col-8 text-muted small">Used So Far</div><div class="col-4 text-end fw-semibold small">' + amount(minimumUsageInfo.used_so_far) + '</div></div>'
                      + '<div class="row"><div class="col-8 text-muted small">Balance</div><div class="col-4 text-end fw-semibold small">' + amount(minimumUsageInfo.balance) + '</div></div>'
                    : '<div class="small text-muted">Not Applicable</div>')
                + '</div>';
        }

        $('#viewSessionModalBody').html(html);
    }

});
</script>
@endsection
