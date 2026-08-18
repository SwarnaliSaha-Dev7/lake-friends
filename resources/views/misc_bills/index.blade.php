@extends('base.app')
@section('title', $title)
@section('page_title', $page_title)

@section('content')

    {{-- Header row --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div></div>
        <div class="d-flex gap-2">
            <a href="{{ route('misc-bills.history') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> History
            </a>
        </div>
    </div>

    {{-- Summary card --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg,#66bb6a,#388e3c);">
                <div>
                    <div class="small opacity-75 mb-1">Today's Misc Sales</div>
                    <div class="fs-5 fw-bold">₹{{ number_format($todayTotal, 0) }}</div>
                </div>
                <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg,#29b6f6,#0288d1);">
                <div>
                    <div class="small opacity-75 mb-1">Today's Bills</div>
                    <div class="fs-5 fw-bold">{{ $todayBills->count() }}</div>
                </div>
                <i class="fa-solid fa-receipt fs-2 opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Billing area --}}
    <div class="row g-3 mb-4">
        {{-- LEFT: Item picker --}}
        <div class="col-lg-7">
            <div class="member-list-part p-3">
                <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Items</h2>
                    <input type="text" id="miscItemSearch" class="form-control form-control-sm shadow-none" style="max-width:220px;" placeholder="Search item...">
                </div>
                @if($miscItems->count())
                <div class="text-muted small mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i> Click an item below to add it to the bill on the right.
                </div>
                @endif
                <div class="d-flex gap-2 flex-wrap mb-3" id="miscCatFilters">
                    <button type="button" class="btn btn-sm btn-primary misc-cat-filter-btn" data-cat="all">All</button>
                    @foreach($categories as $cat)
                        <button type="button" class="btn btn-sm btn-outline-secondary misc-cat-filter-btn" data-cat="{{ $cat->name }}">{{ $cat->name }}</button>
                    @endforeach
                </div>
                <div class="row g-2" id="miscItemGrid">
                    @forelse($miscItems as $item)
                        <div class="col-sm-6 col-xl-4 misc-item-card-wrap" data-cat="{{ $item['category'] }}" data-name="{{ strtolower($item['name']) }}">
                            <div class="border rounded-3 p-2 h-100 misc-item-card" style="cursor:pointer;"
                                data-id="{{ $item['id'] }}"
                                data-name="{{ $item['name'] }}"
                                data-price="{{ $item['price'] }}"
                                data-unit="{{ $item['unit'] }}"
                                data-gst="{{ $item['gst_percentage'] }}"
                                data-editable="{{ $item['is_price_editable'] ? '1' : '0' }}">
                                <div class="fw-semibold small">{{ $item['name'] }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">{{ $item['category'] }} · {{ $item['unit'] }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="fw-bold text-primary">Rs {{ number_format($item['price'], 2) }}</span>
                                    @if($item['is_price_editable'])
                                        <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size:0.62rem;">Editable</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fa-solid fa-box-open mb-2 d-block fs-1 opacity-25"></i>
                            <div class="fw-semibold mb-1">No items to bill yet</div>
                            <div class="small mb-3">Add a misc item (like T-Shirt, Table Tennis, Banquet Rent) first — it'll show up here to bill instantly.</div>
                            <a href="{{ route('manage-misc-items.index') }}" class="btn btn-sm btn-primary fw-semibold">
                                <i class="fa-solid fa-plus me-1"></i> Go to Misc Manage
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT: Cart --}}
        <div class="col-lg-5">
            <div class="member-list-part p-3">
                <div class="fw-semibold mb-2 small text-muted text-uppercase">Bill</div>

                <div id="miscCartEmpty" class="text-muted small text-center py-3">
                    <i class="fa-solid fa-cart-shopping mb-1 d-block fs-4 opacity-25"></i>
                    No items added yet
                </div>
                <div id="miscCartItems"></div>

                <div id="miscCartTotals" style="display:none;">
                    <hr class="my-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold" id="miscCartSubtotal">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">GST</span>
                        <span class="fw-semibold" id="miscCartGst">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-1">
                        <span>Total</span>
                        <span class="text-primary fs-5" id="miscCartTotal">Rs 0.00</span>
                    </div>
                </div>

                <hr class="my-3">

                <div class="mb-2">
                    <label class="form-label small mb-1">Buyer Name <span class="text-muted">(optional)</span></label>
                    <input type="text" id="miscBuyerName" class="form-control form-control-sm shadow-none" placeholder="e.g. Mr. Sharma">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Buyer Contact <span class="text-muted">(optional)</span></label>
                    <input type="text" id="miscBuyerContact" class="form-control form-control-sm shadow-none" placeholder="Phone number">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Event / Booking Date <span class="text-muted">(optional — for advance bookings like Banquet Rent)</span></label>
                    <input type="date" id="miscEventDate" class="form-control form-control-sm shadow-none">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small mb-1">Payment Mode</label>
                        <select id="miscPaymentMode" class="form-select form-select-sm shadow-none">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Reference <span class="text-muted">(optional)</span></label>
                        <input type="text" id="miscPaymentReference" class="form-control form-control-sm shadow-none" placeholder="UPI/Cheque no.">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Remarks / Notes <span class="text-muted">(optional)</span></label>
                    <textarea id="miscRemarks" class="form-control form-control-sm shadow-none" rows="2" placeholder="e.g. size, guest count, any note about this bill"></textarea>
                </div>

                <button type="button" id="placeMiscBillBtn" class="btn btn-primary w-100 fw-semibold mt-2" style="display:none;">
                    <i class="fa-solid fa-check me-1"></i> Generate Bill
                </button>
            </div>
        </div>
    </div>

    {{-- Today's bills table --}}
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <h2 class="fs-5 common-heading mb-3 fw-semibold">Today's Misc Bills</h2>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Bill No</th>
                                <th class="text-white fw-medium text-nowrap">Buyer</th>
                                <th class="text-white fw-medium text-nowrap">Time</th>
                                <th class="text-white fw-medium text-nowrap">Items</th>
                                <th class="text-white fw-medium text-nowrap">Event Date</th>
                                <th class="text-white fw-medium text-nowrap">Amount</th>
                                <th class="text-white fw-medium text-nowrap">Payment</th>
                                <th class="text-white fw-medium text-nowrap">Status</th>
                                <th class="text-white fw-medium text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="miscTodayBillsBody">
                            @forelse($todayBills as $bill)
                                <tr id="misc-bill-row-{{ $bill->id }}">
                                    <td class="text-nowrap fw-medium">{{ $bill->bill_no }}</td>
                                    <td class="text-nowrap">{{ $bill->buyer_name ?: 'Cash Customer' }}</td>
                                    <td class="text-nowrap text-muted small">{{ $bill->created_at->format('h:i A') }}</td>
                                    <td>
                                        @foreach($bill->items as $it)
                                            <div class="small text-nowrap">
                                                {{ $it->miscItem->name ?? '—' }} <span class="text-muted">&times;{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-nowrap">
                                        @if($bill->event_date)
                                            <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2">{{ $bill->event_date->format('d M Y') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($bill->net_amount, 2) }}</td>
                                    <td class="text-nowrap text-capitalize">{{ str_replace('_', ' ', $bill->payment_mode) }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $bill->status === 'paid' ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger' }} misc-bill-status-{{ $bill->id }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('misc-bills.receipt', $bill->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Receipt">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if($bill->status === 'paid')
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1 px-2 py-1 fw-semibold cancel-misc-bill-btn" data-id="{{ $bill->id }}">
                                                Cancel
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="miscEmptyRow">
                                    <td colspan="9" class="text-center text-muted py-4">No misc bills today.</td>
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

    {{-- Cancel Confirm Modal --}}
    <div class="modal fade" id="cancelMiscBillModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Cancel Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this bill?</p>
                    <label class="form-label small mb-1">Reason <span class="text-muted">(optional)</span></label>
                    <textarea id="cancelMiscReason" class="form-control shadow-none" rows="2"></textarea>
                </div>
                <input type="hidden" id="cancel_misc_bill_id" value="">
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelMiscBillBtn">Yes, Cancel Bill</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    var cart = [];

    /* ── Category filter ── */
    $('.misc-cat-filter-btn').on('click', function () {
        $('.misc-cat-filter-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        applyItemFilter();
    });

    $('#miscItemSearch').on('input', function () { applyItemFilter(); });

    function applyItemFilter() {
        var activeCat = $('.misc-cat-filter-btn.btn-primary').data('cat');
        var q = ($('#miscItemSearch').val() || '').toLowerCase();
        $('.misc-item-card-wrap').each(function () {
            var $w = $(this);
            var catOk  = activeCat === 'all' || $w.data('cat') === activeCat;
            var nameOk = !q || String($w.data('name')).indexOf(q) !== -1;
            $w.toggle(catOk && nameOk);
        });
    }

    /* ── Add to cart ── */
    $(document).on('click', '.misc-item-card', function () {
        var $c = $(this);
        var id = $c.data('id');
        var existing = cart.find(function (c) { return c.id === id; });

        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                id: id,
                name: $c.data('name'),
                unit: $c.data('unit'),
                price: parseFloat($c.data('price')),
                gst: parseFloat($c.data('gst')),
                editable: $c.data('editable') == '1',
                quantity: 1,
            });
        }
        renderCart();
    });

    function renderCart() {
        if (!cart.length) {
            $('#miscCartEmpty').show();
            $('#miscCartItems').empty();
            $('#miscCartTotals').hide();
            $('#placeMiscBillBtn').hide();
            return;
        }

        $('#miscCartEmpty').hide();
        $('#placeMiscBillBtn').show();
        $('#miscCartTotals').show();

        var html = '';
        cart.forEach(function (c, idx) {
            html += '<div class="d-flex align-items-center justify-content-between border-bottom py-2 misc-cart-row" data-idx="' + idx + '">'
                + '<div class="flex-grow-1 me-2">'
                +   '<div class="small fw-semibold">' + c.name + '</div>'
                +   '<div class="d-flex align-items-center gap-1 mt-1">'
                +     '<input type="number" class="form-control form-control-sm misc-qty-input" style="width:64px;" min="0.01" step="0.01" value="' + c.quantity + '" data-idx="' + idx + '">'
                +     '<span class="text-muted small">' + c.unit + ' &times;</span>'
                +     '<input type="number" class="form-control form-control-sm misc-price-input" style="width:84px;" min="0" step="0.01" value="' + c.price.toFixed(2) + '" data-idx="' + idx + '" ' + (c.editable ? '' : 'readonly') + '>'
                +   '</div>'
                + '</div>'
                + '<div class="text-end">'
                +   '<div class="fw-semibold small">Rs ' + (c.quantity * c.price).toFixed(2) + '</div>'
                +   '<button type="button" class="btn btn-sm btn-link text-danger p-0 misc-remove-btn" data-idx="' + idx + '"><i class="fa-solid fa-trash"></i></button>'
                + '</div>'
                + '</div>';
        });
        $('#miscCartItems').html(html);

        var subtotal = 0, gst = 0;
        cart.forEach(function (c) {
            var lineTotal = c.quantity * c.price;
            subtotal += lineTotal;
            gst += lineTotal * (c.gst / 100);
        });
        var total = subtotal + gst;

        $('#miscCartSubtotal').text('Rs ' + subtotal.toFixed(2));
        $('#miscCartGst').text('Rs ' + gst.toFixed(2));
        $('#miscCartTotal').text('Rs ' + total.toFixed(2));
    }

    $(document).on('change', '.misc-qty-input', function () {
        var idx = $(this).data('idx');
        var v = parseFloat($(this).val());
        cart[idx].quantity = (v > 0) ? v : 1;
        renderCart();
    });

    $(document).on('change', '.misc-price-input', function () {
        var idx = $(this).data('idx');
        if (!cart[idx].editable) return;
        var v = parseFloat($(this).val());
        cart[idx].price = (v >= 0) ? v : 0;
        renderCart();
    });

    $(document).on('click', '.misc-remove-btn', function () {
        var idx = $(this).data('idx');
        cart.splice(idx, 1);
        renderCart();
    });

    /* ── Place bill ── */
    $('#placeMiscBillBtn').on('click', function () {
        if (!cart.length) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        var items = cart.map(function (c) {
            return { misc_item_id: c.id, quantity: c.quantity, unit_price: c.price };
        });

        $.ajax({
            url: '{{ route("misc-billing.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: items,
                buyer_name: $('#miscBuyerName').val(),
                buyer_contact: $('#miscBuyerContact').val(),
                event_date: $('#miscEventDate').val(),
                payment_mode: $('#miscPaymentMode').val(),
                payment_reference: $('#miscPaymentReference').val(),
                remarks: $('#miscRemarks').val(),
            },
            success: function (response) {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Generate Bill');
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    window.open(response.receipt_url, '_blank');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    toastr.error(response.error || response.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Generate Bill');
                var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Something went wrong.';
                toastr.error(msg);
            }
        });
    });

    /* ── Cancel bill ── */
    $(document).on('click', '.cancel-misc-bill-btn', function () {
        $('#cancel_misc_bill_id').val($(this).data('id'));
        $('#cancelMiscReason').val('');
        $('#cancelMiscBillModal').modal('show');
    });

    $('#confirmCancelMiscBillBtn').on('click', function () {
        var id  = $('#cancel_misc_bill_id').val();
        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ url('misc-bills') }}/" + id + '/cancel',
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}', reason: $('#cancelMiscReason').val() },
            success: function (response) {
                btn.prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#cancelMiscBillModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                toastr.error('Something went wrong.');
            }
        });
    });

});
</script>
@endsection
