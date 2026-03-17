@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Order History</h2>
                </div>

                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Member Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Email</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Order Number</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Total Amount</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                @php
                                    $statusClass = match($order->status) {
                                        'paid'      => 'bg-success-subtle text-success border-success',
                                        'pending'   => 'bg-warning-subtle text-warning border-warning',
                                        'cancelled' => 'bg-danger-subtle text-danger border-danger',
                                        'refunded'  => 'bg-info-subtle text-info border-info',
                                        'delivered' => 'bg-primary-subtle text-primary border-primary',
                                        default     => 'bg-secondary-subtle text-secondary border-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $order->member->name ?? '—' }}</td>
                                    <td class="text-nowrap text-muted small">{{ $order->member->email ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $order->order_no }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($order->net_amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn view-order-btn"
                                            data-id="{{ $order->id }}" title="View order">
                                            <small><i class="fa-regular fa-eye"></i></small>
                                        </button>
                                        <a href="{{ route('restaurant-orders.invoice', $order->id) }}"
                                            class="border-0 bg-light p-1 rounded-3 lh-1 action-btn ms-1 d-inline-flex align-items-center"
                                            title="Download Invoice" target="_blank">
                                            <small><i class="fa-solid fa-download"></i></small>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No past orders found.</td>
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

    {{-- Order View Modal --}}
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-semibold">Order Details</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="viewOrderModalBody">
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

    $(document).on('click', '.view-order-btn', function () {
        var id = $(this).data('id');
        $('#viewOrderModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
        $('#viewOrderModal').modal('show');

        $.get('{{ route("restaurant-orders.show", ":id") }}'.replace(':id', id), function (res) {
            if (res.statusCode !== 200) {
                $('#viewOrderModalBody').html('<p class="text-danger text-center py-4">Failed to load order.</p>');
                return;
            }

            var o     = res.data;
            var items = o.items || [];

            var foodRows   = '';
            var liquorRows = '';

            for (var i = 0; i < items.length; i++) {
                var it       = items[i];
                var itemName = (it.food_item && it.food_item.name) ? it.food_item.name : '—';
                var offerBadge = '';
                if (it.offer_applied) {
                    var of = it.offer_applied;
                    if (of.type_slug === 'b1g1') {
                        offerBadge = ' <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2" style="font-size:0.7rem;">B1G1</span>';
                    } else if (of.type_slug === 'percentage' && of.discount_value) {
                        offerBadge = ' <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2" style="font-size:0.7rem;">' + of.discount_value + '% off</span>';
                    } else if (of.type_slug === 'flat' && of.discount_value) {
                        offerBadge = ' <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2" style="font-size:0.7rem;">Rs ' + of.discount_value + ' off</span>';
                    }
                }

                if (it.unit === 'ml') {
                    liquorRows += '<tr>'
                        + '<td class="text-muted">' + itemName + offerBadge + '</td>'
                        + '<td class="text-center text-muted text-nowrap">' + (it.metadata && it.metadata.volume ? it.metadata.volume : '—') + '</td>'
                        + '<td class="text-center text-muted">' + it.quantity + '</td>'
                        + '<td class="text-end text-muted text-nowrap">Rs ' + parseFloat(it.unit_price).toFixed(2) + '</td>'
                        + '<td class="text-end text-muted text-nowrap">Rs ' + parseFloat(it.total_amount).toFixed(2) + '</td>'
                        + '</tr>';
                } else {
                    foodRows += '<tr>'
                        + '<td class="text-muted">' + itemName + offerBadge + '</td>'
                        + '<td class="text-center text-muted">' + it.quantity + '</td>'
                        + '<td class="text-end text-muted text-nowrap">Rs ' + parseFloat(it.unit_price).toFixed(2) + '</td>'
                        + '<td class="text-end text-muted text-nowrap">Rs ' + parseFloat(it.total_amount).toFixed(2) + '</td>'
                        + '</tr>';
                }
            }

            var memberName  = (o.member && o.member.name)  ? o.member.name  : '—';
            var memberEmail = (o.member && o.member.email) ? o.member.email : '—';
            var orderDate   = new Date(o.created_at).toLocaleDateString('en-IN', {day:'2-digit', month:'2-digit', year:'numeric'});

            var statusColors = { paid:'text-success', pending:'text-warning', delivered:'text-primary', cancelled:'text-danger', refunded:'text-info' };
            var statusClass  = statusColors[o.status] || 'text-muted';

            var thStyle  = 'style="background:#97A0AC; color:#fff; font-weight:500; padding:10px 12px; white-space:nowrap;"';
            var thStyleC = 'style="background:#97A0AC; color:#fff; font-weight:500; padding:10px 12px; text-align:center; white-space:nowrap;"';
            var thStyleR = 'style="background:#97A0AC; color:#fff; font-weight:500; padding:10px 12px; text-align:right; white-space:nowrap;"';

            var foodSection = '';
            if (foodRows) {
                foodSection = '<div class="mb-4">'
                    + '<label class="form-label fw-semibold text-dark mb-2">'
                    + '<span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">'
                    + '<img src="{{ asset("assets/images/d-food-order.svg") }}"></span>Food Order Summary</label>'
                    + '<div class="border rounded-3 overflow-hidden"><div class="table-responsive">'
                    + '<table class="table mb-0 align-middle">'
                    + '<thead><tr>'
                    + '<th ' + thStyle  + '>Item Name</th>'
                    + '<th ' + thStyleC + '>Quantity</th>'
                    + '<th ' + thStyleR + '>Unit Price</th>'
                    + '<th ' + thStyleR + '>Total Price</th>'
                    + '</tr></thead>'
                    + '<tbody>' + foodRows + '</tbody>'
                    + '</table></div></div></div>';
            }

            var liquorSection = '';
            if (liquorRows) {
                liquorSection = '<div class="mb-4">'
                    + '<label class="form-label fw-semibold text-dark mb-2">'
                    + '<span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">'
                    + '<img src="{{ asset("assets/images/d-liquor-order.svg") }}"></span>Liquor Order Summary</label>'
                    + '<div class="border rounded-3 overflow-hidden"><div class="table-responsive">'
                    + '<table class="table mb-0 align-middle">'
                    + '<thead><tr>'
                    + '<th ' + thStyle  + '>Item Name</th>'
                    + '<th ' + thStyleC + '>Volume</th>'
                    + '<th ' + thStyleC + '>Quantity</th>'
                    + '<th ' + thStyleR + '>Unit Price</th>'
                    + '<th ' + thStyleR + '>Total Price</th>'
                    + '</tr></thead>'
                    + '<tbody>' + liquorRows + '</tbody>'
                    + '</table></div></div></div>';
            }

            var statusLabel = o.status.charAt(0).toUpperCase() + o.status.slice(1);

            var html = ''
                + '<div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">'
                +   '<img src="{{ asset("assets/images/logo.svg") }}" style="width:44px;height:44px;" alt="logo">'
                +   '<div>'
                +     '<div class="fw-bold fs-6" style="color:#97A0AC;">Kolkata Lake Friends Club</div>'
                +     '<div class="text-muted" style="font-size:0.72rem;">Kolkata, West Bengal</div>'
                +   '</div>'
                + '</div>'
                + '<div class="row mb-4 gy-2">'
                +   '<div class="col-sm-6">'
                +     '<h5 class="fw-bold mb-1">Invoice</h5>'
                +     '<div class="text-muted small">Invoice No: <span class="text-dark fw-medium">' + (o.bill_no || '—') + '</span></div>'
                +     '<div class="text-muted small">Order Status: <span class="fw-medium ' + statusClass + '">' + statusLabel + '</span>'
                +     ' &nbsp; Billing Date: <span class="text-dark fw-medium">' + orderDate + '</span></div>'
                +   '</div>'
                +   '<div class="col-sm-6 text-sm-end">'
                +     '<h6 class="fw-bold mb-1">Billing to</h6>'
                +     '<div class="text-muted small">' + memberName + '</div>'
                +     '<div class="text-muted small">' + memberEmail + '</div>'
                +   '</div>'
                + '</div>'
                + foodSection
                + liquorSection
                + '<div class="p-3 bg-light border rounded-3">'
                +   '<div class="row mb-2 border-bottom p-2"><div class="col-8 text-end text-muted">Subtotal</div><div class="col-4 text-center fw-semibold">Rs ' + parseFloat(o.taxable_amount).toFixed(2) + '</div></div>'
                +   '<div class="row mb-2 border-bottom p-2"><div class="col-8 text-end text-muted">GST (' + parseFloat(o.gst_percentage).toFixed(0) + '%)</div><div class="col-4 text-center fw-semibold">Rs ' + parseFloat(o.gst_amount).toFixed(2) + '</div></div>'
                +   '<div class="row mb-2 border-bottom p-2"><div class="col-8 text-end text-warning fw-medium">Offer Applied</div><div class="col-4 text-center text-muted fw-semibold">-Rs ' + parseFloat(o.discount_amount).toFixed(2) + '</div></div>'
                +   '<div class="row mt-3 py-2 bg-dark text-white rounded-3 mx-0"><div class="col-8 text-end">Grand Total</div><div class="col-4 text-center fw-bold fs-5">Rs ' + parseFloat(o.net_amount).toFixed(2) + '</div></div>'
                + '</div>';

            $('#viewOrderModalBody').html(html);
        }).fail(function () {
            $('#viewOrderModalBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

});
</script>
@endsection
