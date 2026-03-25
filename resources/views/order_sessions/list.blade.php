@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Today's Sessions</h2>
                    <button class="btn btn-primary btn-sm fw-semibold px-3" id="openSessionBtn">
                        <i class="fa-solid fa-plus me-1"></i> New Order
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%" id="sessionTable">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Session No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Member</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Orders</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Running Total</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sessionTableBody">
                            @forelse($sessions as $session)
                                @php
                                    $pendingTotal = $session->orders->where('status', 'pending')->sum('net_amount');
                                    $orderCount   = $session->orders->whereNotIn('status', ['cancelled'])->count();
                                    $statusClass  = match($session->status) {
                                        'open'      => 'bg-success-subtle text-success border-success',
                                        'billed'    => 'bg-primary-subtle text-primary border-primary',
                                        'cancelled' => 'bg-danger-subtle text-danger border-danger',
                                        default     => 'bg-secondary-subtle text-secondary border-secondary',
                                    };
                                    $isOpen      = $session->status === 'open';
                                    $isBilled    = $session->status === 'billed';
                                    $isCancelled = $session->status === 'cancelled';
                                @endphp
                                <tr id="session-row-{{ $session->id }}">
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $session->session_no }}</td>
                                    <td class="text-nowrap">
                                        <div class="fw-medium">{{ $session->member->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $session->member->member_code ?? '' }}</small>
                                    </td>
                                    <td class="text-nowrap text-center session-order-count">{{ $orderCount }}</td>
                                    <td class="text-nowrap fw-semibold session-pending-total">
                                        Rs {{ number_format($isBilled ? $session->net_amount : $pendingTotal, 2) }}
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $statusClass }} session-status-badge">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{-- View --}}
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn view-session-btn"
                                            data-id="{{ $session->id }}" title="View session details">
                                            <small><i class="fa-regular fa-eye"></i></small>
                                        </button>

                                        @if($isOpen)
                                            {{-- Add Order --}}
                                            <button class="btn btn-outline-primary btn-sm ms-1 fw-semibold px-2 py-1 add-order-btn"
                                                data-session-id="{{ $session->id }}"
                                                data-member-id="{{ $session->member_id }}"
                                                data-member-name="{{ $session->member->name ?? '' }}"
                                                data-member-code="{{ $session->member->member_code ?? '' }}"
                                                title="Add order to session">
                                                <i class="fa-solid fa-plus me-1"></i>Add Order
                                            </button>

                                            {{-- Generate Bill --}}
                                            <button class="btn btn-success btn-sm ms-1 fw-semibold px-2 py-1 generate-bill-btn"
                                                data-id="{{ $session->id }}"
                                                data-session-no="{{ $session->session_no }}"
                                                title="Generate final bill">
                                                <i class="fa-solid fa-receipt me-1"></i>Bill
                                            </button>

                                            {{-- Cancel Session --}}
                                            <button class="btn btn-outline-danger btn-sm ms-1 fw-semibold px-2 py-1 cancel-session-btn"
                                                data-id="{{ $session->id }}"
                                                data-is-billed="0"
                                                title="Cancel session">
                                                <i class="fa-solid fa-xmark me-1"></i>Cancel
                                            </button>
                                        @endif

                                        @if($isBilled)
                                            {{-- Download Invoice --}}
                                            <a href="{{ route('order-sessions.invoice', $session->id) }}"
                                                class="btn btn-outline-secondary btn-sm ms-1 fw-semibold px-2 py-1"
                                                title="Download invoice" target="_blank">
                                                <i class="fa-solid fa-download me-1"></i>Invoice
                                            </a>

                                            {{-- Cancel Billed Session --}}
                                            <button class="btn btn-outline-danger btn-sm ms-1 fw-semibold px-2 py-1 cancel-session-btn"
                                                data-id="{{ $session->id }}"
                                                data-is-billed="1"
                                                data-amount="Rs {{ number_format($session->net_amount, 2) }}"
                                                title="Cancel & refund">
                                                <i class="fa-solid fa-rotate-left me-1"></i>Cancel & Refund
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center text-muted py-4">No sessions today.</td>
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

    {{-- New Session Modal --}}
    <div class="modal fade" id="newSessionModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Open New Session</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <label class="form-label fw-medium">Select Member</label>
                    <select class="form-select" id="sessionMemberSelect">
                        <option value="">— Select member —</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->member_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="confirmOpenSessionBtn">Open Session</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Generate Bill Confirm Modal --}}
    <div class="modal fade" id="generateBillModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width:56px;height:56px;background:#d1fae5;">
                            <i class="fa-solid fa-receipt fs-4 text-success"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold mb-1">Generate Final Bill?</h5>
                    <p class="text-muted small mb-4">This will close the session and deduct the total from the member's wallet.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-light px-4" data-bs-dismiss="modal">Back</button>
                        <button class="btn btn-success px-4" id="confirmBillBtn">Yes, Generate Bill</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Session Confirm Modal --}}
    <div class="modal fade" id="cancelSessionModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width:56px;height:56px;background:#fee2e2;">
                            <i class="fa-solid fa-rotate-left fs-4 text-danger"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold mb-1" id="cancelSessionTitle">Cancel Session?</h5>
                    <p class="text-muted small mb-1" id="cancelSessionDesc">This will cancel all pending orders.</p>
                    <p class="fw-bold text-danger mb-3 d-none" id="cancelSessionRefund"></p>
                    <p class="text-muted small mb-4">This action cannot be undone.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-light px-4" data-bs-dismiss="modal">Keep Session</button>
                        <button class="btn btn-danger px-4" id="confirmCancelSessionBtn">Yes, Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Session View Modal --}}
    <div class="modal fade" id="viewSessionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-semibold">Session Details</h2>
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

    /* ── Open New Session ──────────────────────────────────────────── */
    $('#openSessionBtn').on('click', function () {
        $('#sessionMemberSelect').val('');
        $('#newSessionModal').modal('show');
    });

    $('#confirmOpenSessionBtn').on('click', function () {
        var memberId = $('#sessionMemberSelect').val();
        if (!memberId) { toastr.warning('Please select a member.'); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Opening...');

        $.ajax({
            url:         '{{ route("order-sessions.store") }}',
            type:        'POST',
            contentType: 'application/json',
            data: JSON.stringify({ _token: '{{ csrf_token() }}', member_id: memberId }),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#newSessionModal').modal('hide');
                    appendSessionRow(res.session);
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Open Session');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Open Session');
            }
        });
    });

    /* ── Add Order to Session ──────────────────────────────────────── */
    $(document).on('click', '.add-order-btn', function () {
        var $btn = $(this);
        window.currentOrderSession = {
            id:         $btn.data('session-id'),
            memberName: $btn.data('member-name'),
            memberCode: $btn.data('member-code'),
            memberId:   $btn.data('member-id'),
        };

        // Populate the global createOrderModal with session member info
        $('#cardentry').data('member-id', window.currentOrderSession.memberId);
        $('#cardMemberName').text(window.currentOrderSession.memberName);
        $('#cardMemberCode').text(window.currentOrderSession.memberCode);
        $('#cardMemberPlan').text('');

        // Trigger the existing createOrderBtn logic
        $('#createOrderBtn').trigger('click');
    });

    /* ── Listen for session order added event ──────────────────────── */
    $(document).on('sessionOrderAdded', function (e, data) {
        var $row = $('#session-row-' + data.sessionId);
        $row.find('.session-pending-total').text('Rs ' + data.pendingTotal);
        var count = parseInt($row.find('.session-order-count').text()) || 0;
        $row.find('.session-order-count').text(count + 1);
    });

    /* ── Generate Bill ─────────────────────────────────────────────── */
    $(document).on('click', '.generate-bill-btn', function () {
        $('#confirmBillBtn').data('id', $(this).data('id'));
        $('#generateBillModal').modal('show');
    });

    $('#confirmBillBtn').on('click', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url:  '{{ route("order-sessions.generate-bill", ":id") }}'.replace(':id', id),
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#generateBillModal').modal('hide');
                    updateSessionRowBilled(id, res);
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Yes, Generate Bill');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Yes, Generate Bill');
            }
        });
    });

    /* ── Cancel Session ────────────────────────────────────────────── */
    $(document).on('click', '.cancel-session-btn', function () {
        var id       = $(this).data('id');
        var isBilled = $(this).data('is-billed') == 1;
        var amount   = $(this).data('amount');

        $('#confirmCancelSessionBtn').data('id', id);
        if (isBilled) {
            $('#cancelSessionTitle').text('Cancel & Refund?');
            $('#cancelSessionDesc').text('This will cancel the session and refund');
            $('#cancelSessionRefund').text(amount).removeClass('d-none');
        } else {
            $('#cancelSessionTitle').text('Cancel Session?');
            $('#cancelSessionDesc').text('This will cancel all pending orders in this session.');
            $('#cancelSessionRefund').addClass('d-none');
        }
        $('#cancelSessionModal').modal('show');
    });

    $('#confirmCancelSessionBtn').on('click', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url:  '{{ route("order-sessions.cancel", ":id") }}'.replace(':id', id),
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#cancelSessionModal').modal('hide');
                    updateSessionRowCancelled(id);
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Yes, Cancel');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Yes, Cancel');
            }
        });
    });

    /* ── View Session ──────────────────────────────────────────────── */
    $(document).on('click', '.view-session-btn', function () {
        var id = $(this).data('id');
        $('#viewSessionModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
        $('#viewSessionModal').modal('show');

        $.get('{{ route("order-sessions.show", ":id") }}'.replace(':id', id), function (res) {
            if (res.statusCode !== 200) {
                $('#viewSessionModalBody').html('<p class="text-danger text-center py-4">Failed to load session.</p>');
                return;
            }
            renderSessionModal(res.data, res.pending_total, res.wallet_balance);
        }).fail(function () {
            $('#viewSessionModalBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

    /* ── Helpers ───────────────────────────────────────────────────── */
    function appendSessionRow(session) {
        $('#emptyRow').remove();
        var sl     = $('#sessionTableBody tr').length + 1;
        var row    = '<tr id="session-row-' + session.id + '">'
            + '<td class="text-nowrap">'  + sl + '</td>'
            + '<td class="text-nowrap fw-medium">' + session.session_no + '</td>'
            + '<td class="text-nowrap"><div class="fw-medium">' + session.member_name + '</div>'
            +   '<small class="text-muted">' + session.member_code + '</small></td>'
            + '<td class="text-nowrap text-center session-order-count">0</td>'
            + '<td class="text-nowrap fw-semibold session-pending-total">Rs 0.00</td>'
            + '<td class="text-nowrap"><span class="badge border rounded-pill px-3 py-1 bg-success-subtle text-success border-success session-status-badge">Open</span></td>'
            + '<td class="text-nowrap">'
            +   '<button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn view-session-btn" data-id="' + session.id + '" title="View"><small><i class="fa-regular fa-eye"></i></small></button>'
            +   '<button class="btn btn-outline-primary btn-sm ms-1 fw-semibold px-2 py-1 add-order-btn"'
            +     ' data-session-id="' + session.id + '" data-member-id="' + session.member_id + '"'
            +     ' data-member-name="' + session.member_name + '" data-member-code="' + session.member_code + '">'
            +     '<i class="fa-solid fa-plus me-1"></i>Add Order</button>'
            +   '<button class="btn btn-success btn-sm ms-1 fw-semibold px-2 py-1 generate-bill-btn"'
            +     ' data-id="' + session.id + '" data-session-no="' + session.session_no + '">'
            +     '<i class="fa-solid fa-receipt me-1"></i>Bill</button>'
            +   '<button class="btn btn-outline-danger btn-sm ms-1 fw-semibold px-2 py-1 cancel-session-btn"'
            +     ' data-id="' + session.id + '" data-is-billed="0">'
            +     '<i class="fa-solid fa-xmark me-1"></i>Cancel</button>'
            + '</td></tr>';
        $('#sessionTableBody').prepend(row);
    }

    function updateSessionRowBilled(id, res) {
        var $row = $('#session-row-' + id);
        $row.find('.session-status-badge')
            .removeClass()
            .addClass('badge border rounded-pill px-3 py-1 bg-primary-subtle text-primary border-primary session-status-badge')
            .text('Billed');
        $row.find('.session-pending-total').text('Rs ' + res.net_amount);
        var invoiceUrl = '{{ route("order-sessions.invoice", ":id") }}'.replace(':id', id);
        $row.find('.add-order-btn, .generate-bill-btn').remove();
        $row.find('.cancel-session-btn')
            .attr('data-is-billed', '1')
            .attr('data-amount', 'Rs ' + res.net_amount)
            .html('<i class="fa-solid fa-rotate-left me-1"></i>Cancel & Refund');
        $row.find('.cancel-session-btn').before(
            '<a href="' + invoiceUrl + '" class="btn btn-outline-secondary btn-sm ms-1 fw-semibold px-2 py-1" target="_blank">'
            + '<i class="fa-solid fa-download me-1"></i>Invoice</a>'
        );
    }

    function updateSessionRowCancelled(id) {
        var $row = $('#session-row-' + id);
        $row.find('.session-status-badge')
            .removeClass()
            .addClass('badge border rounded-pill px-3 py-1 bg-danger-subtle text-danger border-danger session-status-badge')
            .text('Cancelled');
        $row.find('.add-order-btn, .generate-bill-btn, .cancel-session-btn').remove();
    }

    function renderSessionModal(session, pendingTotal, walletBalance) {
        var statusColor = { open: 'text-success', billed: 'text-primary', cancelled: 'text-danger' };
        var sc = statusColor[session.status] || 'text-muted';

        var html = '<div class="mb-3 pb-2 border-bottom d-flex justify-content-between align-items-start">'
            + '<div>'
            + '<div class="fw-bold fs-6">' + session.session_no + '</div>'
            + '<div class="text-muted small">' + (session.member ? session.member.name : '—') + '</div>'
            + '</div>'
            + '<span class="fw-semibold ' + sc + '">' + session.status.charAt(0).toUpperCase() + session.status.slice(1) + '</span>'
            + '</div>';

        var orders = session.orders || [];
        if (!orders.length) {
            html += '<p class="text-muted text-center py-3">No orders in this session.</p>';
        } else {
            orders.forEach(function (order, idx) {
                if (order.status === 'cancelled') return;
                var stClass = { paid: 'text-success', pending: 'text-warning', cancelled: 'text-danger' }[order.status] || 'text-muted';
                var items   = order.items || [];
                var itemRows = '';
                items.forEach(function (it) {
                    var name = it.food_item ? it.food_item.name : '—';
                    var vol  = it.unit === 'btl' ? '1 BTL' : (it.metadata && it.metadata.volume_ml ? it.metadata.volume_ml + 'ml' : '');
                    var offerBadge = '';
                    if (it.offer_applied) {
                        var of = it.offer_applied;
                        if (of.type_slug === 'b1g1') {
                            offerBadge = ' <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2" style="font-size:0.65rem;">B1G1</span>';
                        } else if (of.type_slug === 'percentage' && of.discount_value) {
                            offerBadge = ' <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2" style="font-size:0.65rem;">' + of.discount_value + '% off</span>';
                        } else if (of.type_slug === 'flat' && of.discount_value) {
                            offerBadge = ' <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2" style="font-size:0.65rem;">Rs ' + of.discount_value + ' off</span>';
                        }
                    }
                    itemRows += '<tr>'
                        + '<td class="text-muted small">' + name + offerBadge + '</td>'
                        + '<td class="text-center text-muted small">' + (vol || it.unit) + '</td>'
                        + '<td class="text-center text-muted small">' + it.quantity + '</td>'
                        + '<td class="text-end text-muted small">Rs ' + parseFloat(it.total_amount).toFixed(2) + '</td>'
                        + '</tr>';
                });
                html += '<div class="mb-3 border rounded-3 overflow-hidden">'
                    + '<div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background:#f8f9fa;">'
                    + '<span class="fw-semibold small">Round ' + (idx + 1) + ' — ' + order.order_no + '</span>'
                    + '<span class="small fw-medium ' + stClass + '">' + order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span>'
                    + '</div>'
                    + '<table class="table table-sm mb-0"><thead><tr>'
                    + '<th style="font-size:0.75rem;">Item</th><th class="text-center" style="font-size:0.75rem;">Vol</th>'
                    + '<th class="text-center" style="font-size:0.75rem;">Qty</th><th class="text-end" style="font-size:0.75rem;">Total</th>'
                    + '</tr></thead><tbody>' + itemRows + '</tbody></table>'
                    + '<div class="px-3 py-2 text-end border-top small fw-semibold text-muted">Round Total: Rs ' + parseFloat(parseFloat(order.taxable_amount) - parseFloat(order.discount_amount)).toFixed(2) + '</div>'
                    + '</div>';
            });
        }

        // Compute session-level subtotal, discount, gst from orders
        var sessionSubtotal  = 0, sessionDiscount = 0, sessionGst = 0;
        (session.orders || []).forEach(function (o) {
            if (o.status === 'cancelled') return;
            sessionSubtotal  += parseFloat(o.taxable_amount)  || 0;
            sessionDiscount  += parseFloat(o.discount_amount) || 0;
            sessionGst       += parseFloat(o.gst_amount)      || 0;
        });
        var sessionNet = sessionSubtotal - sessionDiscount + sessionGst;

        html += '<div class="p-3 bg-light border rounded-3 mt-2">'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">Subtotal</div>'
            + '<div class="col-4 text-center fw-semibold small">Rs ' + sessionSubtotal.toFixed(2) + '</div></div>'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">GST (10%)</div>'
            + '<div class="col-4 text-center fw-semibold small">Rs ' + sessionGst.toFixed(2) + '</div></div>'
            + (sessionDiscount > 0
                ? '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-warning small fw-medium">Offer Applied</div>'
                  + '<div class="col-4 text-center fw-semibold small text-muted">-Rs ' + sessionDiscount.toFixed(2) + '</div></div>'
                : '')
            + '<div class="row py-1 bg-dark text-white rounded-3 mx-0 mt-1"><div class="col-8 text-end small">Grand Total</div>'
            + '<div class="col-4 text-center fw-bold">Rs ' + sessionNet.toFixed(2) + '</div></div>'
            + '<div class="row mt-2"><div class="col-8 text-end text-muted small">Wallet Balance</div>'
            + '<div class="col-4 text-center fw-semibold text-success small">Rs ' + walletBalance + '</div></div>'
            + '</div>';

        $('#viewSessionModalBody').html(html);
    }

});
</script>
@endsection
