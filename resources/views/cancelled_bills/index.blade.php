@extends('base.app')

@section('title', 'Cancelled Bills')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="member-list-part position-relative">

            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                <h2 class="fs-5 common-heading mb-0 fw-semibold">Cancelled Bills</h2>
            </div>

            <div class="table-responsive">
                <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                            <th class="text-white fw-medium align-middle text-nowrap">Order No</th>
                            <th class="text-white fw-medium align-middle text-nowrap">Member</th>
                            <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                            <th class="text-white fw-medium align-middle text-nowrap">Cancelled By</th>
                            <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td class="text-nowrap">{{ $loop->iteration }}</td>
                            <td class="text-nowrap fw-medium">{{ $session->session_no }}</td>
                            <td class="text-nowrap">{{ $session->member->name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $session->created_at->format('d M Y') }}</td>
                            <td class="text-nowrap">{{ $session->cancelledBy->name ?? '—' }}</td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-primary btn-sm fw-semibold px-3 cb-edit-btn"
                                    data-id="{{ $session->id }}"
                                    data-session-no="{{ $session->session_no }}"
                                    data-member="{{ $session->member->name ?? '—' }}"
                                    data-date="{{ $session->created_at->format('d M Y') }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit Order
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No cancelled bills found for this financial year.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Reorder Modal --}}
<div class="modal fade" id="cbOrderModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-5 fw-semibold">Edit Order</h2>
                <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-regular fa-circle-xmark"></i>
                </button>
            </div>
            <div class="modal-body">

                <!-- Member & Order Info -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h5 class="fw-bold mb-0" id="cbOrderMemberName">—</h5>
                        <p class="text-muted small mb-0" id="cbOrderMeta">—</p>
                    </div>
                    <div class="text-end text-muted small">
                        <div>Order Date: <strong id="cbOrderDate">—</strong></div>
                    </div>
                </div>

                <!-- Food Order Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="member-list-part position-relative">
                            <label class="form-label fw-semibold text-dark mb-3">
                                <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                    <img src="{{ asset('assets/images/d-food-order.svg') }}">
                                </span>Food Order Summary
                            </label>
                            <div class="table-responsive">
                                <table class="table rounded-3 overflow-hidden" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Quantity</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Unit Price</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Offer</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Total</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cbFoodTableBody">
                                        <tr id="cbFoodEmptyRow">
                                            <td colspan="6" class="text-center text-muted py-3 small">No food items added.</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-end mt-2">
                                    <button class="btn btn-info btn-sm cb-add-food-item" type="button">+ Add Item</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liquor Order Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="member-list-part position-relative">
                            <label class="form-label fw-semibold text-dark mb-3">
                                <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                    <img src="{{ asset('assets/images/d-liquor-order.svg') }}">
                                </span>Liquor Order Summary
                            </label>
                            <div class="table-responsive">
                                <table class="table rounded-3 overflow-hidden" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Volume</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Quantity</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Unit Price</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Offer</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Total Price</th>
                                            <th class="text-white fw-medium align-middle text-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cbLiquorTableBody">
                                        <tr id="cbLiquorEmptyRow">
                                            <td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-end mt-2">
                                    <button class="btn btn-info btn-sm cb-add-liquor-item" type="button">+ Add Item</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="total-section">
                    <div class="mt-4 p-3 bg-light border rounded-3">
                        <div class="row mb-2 border-bottom p-2">
                            <div class="col-8 text-end text-muted">Subtotal</div>
                            <div class="col-4 text-center fw-semibold" id="cbOrderSubtotal">Rs 0</div>
                        </div>
                        <div class="row mb-2 border-bottom p-2">
                            <div class="col-8 text-end text-muted">GST (5%)</div>
                            <div class="col-4 text-center fw-semibold" id="cbOrderGst">Rs 0</div>
                        </div>
                        <div class="row mb-2 border-bottom p-2">
                            <div class="col-8 text-end text-warning fw-medium">Offer applied</div>
                            <div class="col-4 text-center text-muted fw-semibold" id="cbOrderOfferApplied">-Rs 0</div>
                        </div>
                        <div class="row mt-3 py-2 bg-dark text-white rounded-3 mx-0">
                            <div class="col-8 text-end">Grand Total</div>
                            <div class="col-4 text-center fw-bold fs-5" id="cbOrderGrandTotal">Rs 0</div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="cbPlaceOrderBtn">Place Order</button>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('customJS')
<script>
$(function () {
    var GST_RATE       = 0.05;
    var cbFoodItems    = [];
    var cbLiquorItems  = [];
    var cbFoodOfferMap  = {};
    var cbLiquorOfferMap = {};
    var cbItemsLoaded  = false;
    var cbSessionId    = null;

    /* ── Open modal on Edit click ── */
    $(document).on('click', '.cb-edit-btn', function () {
        cbSessionId = $(this).data('id');
        var memberName = $(this).data('member');
        var sessionNo  = $(this).data('session-no');
        var date       = $(this).data('date');

        $('#cbOrderMemberName').text(memberName);
        $('#cbOrderMeta').text('Session: ' + sessionNo);
        $('#cbOrderDate').text(date);
        $('#cbFoodTableBody').html('<tr id="cbFoodEmptyRow"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
        $('#cbLiquorTableBody').html('<tr id="cbLiquorEmptyRow"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
        cbRecalc();
        $('#cbOrderModal').modal('show');

        if (!cbItemsLoaded) {
            $.get('{{ route("getOrderItems") }}', function (res) {
                if (res.statusCode == 200) {
                    cbFoodItems    = res.foodItems;
                    cbLiquorItems  = res.liquorItems;
                    cbItemsLoaded  = true;
                }
                cbAddFoodRow();
            });
        } else {
            cbAddFoodRow();
        }
    });

    /* ── Build option HTML ── */
    function cbBuildFoodOptions() {
        var html = '<option value="">-- Select Item --</option>';
        cbFoodItems.forEach(function (it) {
            var pr = (it.food_item_price && it.food_item_price.price) ? it.food_item_price.price : 0;
            cbFoodOfferMap[it.id] = it.offer || null;
            html += '<option value="' + it.id + '" data-price="' + pr + '">' + it.name + '</option>';
        });
        return html;
    }
    function cbBuildLiquorOptions() {
        var html = '<option value="">-- Select Item --</option>';
        cbLiquorItems.forEach(function (it) {
            cbLiquorOfferMap[it.id] = it.offer || null;
            html += '<option value="' + it.id + '"'
                + ' data-food-item-id="' + it.food_item_id + '"'
                + ' data-price="' + it.price + '"'
                + ' data-is-beer="' + (it.is_beer ? '1' : '0') + '"'
                + ' data-volume-ml="' + (it.volume_ml || 0) + '"'
                + ' data-bar-stock="' + (it.bar_stock || 0) + '">'
                + it.name + '</option>';
        });
        return html;
    }

    /* ── Row HTML ── */
    function cbBuildFoodRow() {
        return '<tr class="cb-food-order-row">'
            + '<td style="width:40%;"><select class="form-select form-select-sm cb-food-item-sel shadow-none">' + cbBuildFoodOptions() + '</select></td>'
            + '<td><div class="input-group input-group-sm" style="width:100px;">'
            + '<button class="btn btn-outline-warning fw-bold py-0 cb-food-qty-minus" type="button">-</button>'
            + '<input type="text" class="form-control text-center border-warning px-1 cb-food-qty-input" value="1" readonly>'
            + '<button class="btn btn-outline-warning fw-bold py-0 cb-food-qty-plus" type="button">+</button>'
            + '</div></td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning cb-food-unit-price" value="Rs 0" readonly></td>'
            + '<td class="text-nowrap cb-food-offer text-muted small">—</td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning cb-food-total-price" value="Rs 0" readonly></td>'
            + '<td><button class="border-0 bg-light p-1 rounded-3 lh-1 cb-delete-food-row" type="button" title="Delete"><small><i class="fa-solid fa-trash"></i></small></button></td>'
            + '</tr>';
    }
    function cbBuildLiquorRow() {
        return '<tr class="cb-liquor-order-row">'
            + '<td style="width:32%;"><select class="form-select form-select-sm cb-liquor-item-sel shadow-none">' + cbBuildLiquorOptions() + '</select></td>'
            + '<td class="cb-liquor-peg-cell" style="min-width:140px;">'
            +   '<span class="text-muted small">Select item first</span>'
            +   '<input type="hidden" class="cb-liquor-volume-ml" value="">'
            +   '<input type="hidden" class="cb-liquor-is-beer" value="">'
            + '</td>'
            + '<td><div class="input-group input-group-sm" style="width:100px;">'
            + '<button class="btn btn-outline-warning fw-bold py-0 cb-liquor-qty-minus" type="button">-</button>'
            + '<input type="text" class="form-control text-center border-warning px-1 cb-liquor-qty-input" value="1" readonly>'
            + '<button class="btn btn-outline-warning fw-bold py-0 cb-liquor-qty-plus" type="button">+</button>'
            + '</div></td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning cb-liquor-unit-price" value="Rs 0" readonly></td>'
            + '<td class="text-nowrap cb-liquor-offer text-muted small">—</td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning cb-liquor-total-price" value="Rs 0" readonly></td>'
            + '<td><button class="border-0 bg-light p-1 rounded-3 lh-1 cb-delete-liquor-row" type="button" title="Delete"><small><i class="fa-solid fa-trash"></i></small></button></td>'
            + '</tr>';
    }

    /* ── Add rows ── */
    function cbAddFoodRow() {
        $('#cbFoodEmptyRow').remove();
        var $row = $(cbBuildFoodRow());
        $('#cbFoodTableBody').append($row);
        $row.find('.cb-food-item-sel').select2({ dropdownParent: $('body'), placeholder: 'Search food item...', allowClear: true, width: '100%' });
        cbRecalc();
    }
    function cbAddLiquorRow() {
        $('#cbLiquorEmptyRow').remove();
        var $row = $(cbBuildLiquorRow());
        $('#cbLiquorTableBody').append($row);
        $row.find('.cb-liquor-item-sel').select2({ dropdownParent: $('body'), placeholder: 'Search liquor item...', allowClear: true, width: '100%' });
        cbRecalc();
    }

    $(document).on('click', '.cb-add-food-item',   function () { cbAddFoodRow();   });
    $(document).on('click', '.cb-add-liquor-item', function () { cbAddLiquorRow(); });

    /* ── Helpers ── */
    function cbReadOffer($opt, map) {
        var offer = (map && $opt.val()) ? (map[$opt.val()] || null) : null;
        return {
            price:  parseFloat($opt.attr('data-price')) || 0,
            ofType: offer ? (offer.type_slug      || '') : '',
            ofVal:  offer ? (offer.discount_value || 0)  : 0,
            buyQty: offer ? (offer.buy_qty        || 1)  : 1,
            getQty: offer ? (offer.get_qty        || 1)  : 1,
        };
    }
    function cbRowDiscount(price, ofType, ofVal, qty, buyQty, getQty) {
        qty = parseInt(qty) || 1; buyQty = parseInt(buyQty) || 1; getQty = parseInt(getQty) || 1;
        if (ofType === 'percentage' && ofVal > 0) return price * (ofVal / 100) * qty;
        if (ofType === 'flat'       && ofVal > 0) return Math.min(ofVal, price) * qty;
        if (ofType === 'b1g1') return Math.floor(qty / (buyQty + getQty)) * getQty * price;
        return 0;
    }
    function cbOfferBadge(ofType, ofVal, buyQty, getQty) {
        buyQty = parseInt(buyQty) || 1; getQty = parseInt(getQty) || 1;
        if (ofType === 'b1g1')              return '<span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 py-1">Buy ' + buyQty + ' Get ' + getQty + ' Free</span>';
        if (ofType === 'percentage' && ofVal > 0) return '<span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">' + ofVal + '% off</span>';
        if (ofType === 'flat'       && ofVal > 0) return '<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">Rs ' + ofVal + ' off</span>';
        return '<span class="text-muted small">—</span>';
    }

    /* ── Food item change ── */
    $(document).on('change', '.cb-food-item-sel', function () {
        var o = cbReadOffer($(this).find('option:selected'), cbFoodOfferMap);
        var $row = $(this).closest('tr');
        var qty = parseInt($row.find('.cb-food-qty-input').val()) || 1;
        var disc = cbRowDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.cb-food-unit-price').val('Rs ' + o.price.toFixed(2));
        $row.find('.cb-food-offer').html(cbOfferBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
        $row.find('.cb-food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        cbRecalc();
    });

    /* ── Food qty +/- ── */
    $(document).on('click', '.cb-food-qty-plus', function () {
        var $inp = $(this).closest('tr').find('.cb-food-qty-input');
        $inp.val(parseInt($inp.val()) + 1);
        cbUpdateFoodRowTotal($(this).closest('tr'));
    });
    $(document).on('click', '.cb-food-qty-minus', function () {
        var $inp = $(this).closest('tr').find('.cb-food-qty-input');
        $inp.val(Math.max(1, parseInt($inp.val()) - 1));
        cbUpdateFoodRowTotal($(this).closest('tr'));
    });
    function cbUpdateFoodRowTotal($row) {
        var o = cbReadOffer($row.find('.cb-food-item-sel option:selected'), cbFoodOfferMap);
        var qty = parseInt($row.find('.cb-food-qty-input').val()) || 1;
        var disc = cbRowDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.cb-food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        cbRecalc();
    }

    /* ── Liquor item change ── */
    $(document).on('change', '.cb-liquor-item-sel', function () {
        var $opt     = $(this).find('option:selected');
        var $row     = $(this).closest('tr');
        var isBeer   = $opt.attr('data-is-beer') == '1';
        var volumeMl = parseInt($opt.attr('data-volume-ml')) || 0;
        var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
        var itemId   = $opt.val();
        var $cell    = $row.find('.cb-liquor-peg-cell');

        if (!itemId) {
            $cell.html('<span class="text-muted small">Select item first</span><input type="hidden" class="cb-liquor-volume-ml" value=""><input type="hidden" class="cb-liquor-is-beer" value="">');
            $row.find('.cb-liquor-unit-price').val('Rs 0');
            $row.find('.cb-liquor-total-price').val('Rs 0');
            cbRecalc(); return;
        }
        if (isBeer) {
            var stockLabel = barStock > 0 ? '<span class="text-success small ms-1">Stock: ' + barStock + ' BTL</span>' : '<span class="text-danger small ms-1">Out of stock</span>';
            $cell.html('<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">1 BTL</span>' + stockLabel + '<input type="hidden" class="cb-liquor-volume-ml" value=""><input type="hidden" class="cb-liquor-is-beer" value="1">');
        } else {
            var stockLabel2 = barStock > 0 ? '<span class="text-success small ms-1">Stock: ' + barStock + 'ml</span>' : '<span class="text-danger small ms-1">Out of stock</span>';
            $cell.html('<span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-2 py-1">' + volumeMl + 'ml</span>' + stockLabel2 + '<input type="hidden" class="cb-liquor-volume-ml" value="' + volumeMl + '"><input type="hidden" class="cb-liquor-is-beer" value="0">');
        }
        cbUpdateLiquorRowTotal($row);
    });

    /* ── Liquor qty +/- ── */
    $(document).on('click', '.cb-liquor-qty-plus', function () {
        var $row     = $(this).closest('tr');
        var $inp     = $row.find('.cb-liquor-qty-input');
        var $opt     = $row.find('.cb-liquor-item-sel option:selected');
        var isBeer   = $row.find('.cb-liquor-is-beer').val() === '1';
        var volumeMl = parseInt($row.find('.cb-liquor-volume-ml').val()) || 0;
        var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
        var curQty   = parseInt($inp.val()) || 1;
        var maxQty   = isBeer ? barStock : (volumeMl > 0 ? Math.floor(barStock / volumeMl) : 0);
        if (maxQty > 0 && curQty >= maxQty) { toastr.warning('Stock limit reached.'); return; }
        $inp.val(curQty + 1);
        cbUpdateLiquorRowTotal($row);
    });
    $(document).on('click', '.cb-liquor-qty-minus', function () {
        var $inp = $(this).closest('tr').find('.cb-liquor-qty-input');
        $inp.val(Math.max(1, parseInt($inp.val()) - 1));
        cbUpdateLiquorRowTotal($(this).closest('tr'));
    });
    function cbUpdateLiquorRowTotal($row) {
        var o    = cbReadOffer($row.find('.cb-liquor-item-sel option:selected'), cbLiquorOfferMap);
        var qty  = parseInt($row.find('.cb-liquor-qty-input').val()) || 1;
        var disc = cbRowDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.cb-liquor-unit-price').val('Rs ' + o.price.toFixed(2));
        $row.find('.cb-liquor-offer').html(cbOfferBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
        $row.find('.cb-liquor-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        cbRecalc();
    }

    /* ── Delete rows ── */
    $(document).on('click', '.cb-delete-food-row', function () {
        $(this).closest('tr').remove();
        if (!$('#cbFoodTableBody .cb-food-order-row').length)
            $('#cbFoodTableBody').html('<tr id="cbFoodEmptyRow"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
        cbRecalc();
    });
    $(document).on('click', '.cb-delete-liquor-row', function () {
        $(this).closest('tr').remove();
        if (!$('#cbLiquorTableBody .cb-liquor-order-row').length)
            $('#cbLiquorTableBody').html('<tr id="cbLiquorEmptyRow"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
        cbRecalc();
    });

    /* ── Recalc ── */
    function cbRecalc() {
        var foodSub = 0, foodDisc = 0, liquorSub = 0, liquorDisc = 0;
        $('#cbFoodTableBody .cb-food-order-row').each(function () {
            var o = cbReadOffer($(this).find('.cb-food-item-sel option:selected'), cbFoodOfferMap);
            var q = parseInt($(this).find('.cb-food-qty-input').val()) || 0;
            foodSub  += o.price * q;
            foodDisc += cbRowDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });
        $('#cbLiquorTableBody .cb-liquor-order-row').each(function () {
            var o = cbReadOffer($(this).find('.cb-liquor-item-sel option:selected'), cbLiquorOfferMap);
            var q = parseInt($(this).find('.cb-liquor-qty-input').val()) || 0;
            liquorSub  += o.price * q;
            liquorDisc += cbRowDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });
        var sub   = foodSub + liquorSub;
        var disc  = foodDisc + liquorDisc;
        var gst   = Math.round((foodSub - foodDisc) * GST_RATE * 100) / 100;
        var grand = sub - disc + gst;
        $('#cbOrderSubtotal').text('Rs ' + sub.toFixed(2));
        $('#cbOrderOfferApplied').text('-Rs ' + disc.toFixed(2));
        $('#cbOrderGst').text('Rs ' + gst.toFixed(2));
        $('#cbOrderGrandTotal').text('Rs ' + grand.toFixed(2));

    }

    /* ── Submit ── */
    $('#cbPlaceOrderBtn').on('click', function () {
        var items = [];
        var valid = true;

        $('#cbFoodTableBody .cb-food-order-row').each(function () {
            var $opt   = $(this).find('.cb-food-item-sel option:selected');
            var itemId = $opt.val();
            if (!itemId) { valid = false; return false; }
            var o    = cbReadOffer($opt, cbFoodOfferMap);
            var qty  = parseInt($(this).find('.cb-food-qty-input').val()) || 1;
            var disc = cbRowDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            items.push({
                food_item_id:  itemId,
                quantity:      qty,
                unit:          'plate',
                unit_price:    o.price,
                offer_applied: o.ofType ? { type_slug: o.ofType, discount_value: o.ofVal, buy_qty: o.buyQty, get_qty: o.getQty } : null,
                total_amount:  parseFloat((o.price * qty - disc).toFixed(2)),
            });
        });

        $('#cbLiquorTableBody .cb-liquor-order-row').each(function () {
            var $opt      = $(this).find('.cb-liquor-item-sel option:selected');
            var itemId    = $opt.val();
            if (!itemId) { valid = false; return false; }
            var foodItemId = $opt.attr('data-food-item-id') || itemId;
            var isBeer    = $(this).find('.cb-liquor-is-beer').val() === '1';
            var volumeMl  = parseInt($(this).find('.cb-liquor-volume-ml').val()) || 0;
            var o         = cbReadOffer($opt, cbLiquorOfferMap);
            var qty       = parseInt($(this).find('.cb-liquor-qty-input').val()) || 1;
            var disc      = cbRowDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            items.push({
                food_item_id:  foodItemId,
                quantity:      qty,
                unit:          isBeer ? 'btl' : 'ml',
                is_beer:       isBeer,
                volume_ml:     isBeer ? null : volumeMl,
                deduct_qty:    isBeer ? qty : qty * volumeMl,
                unit_price:    o.price,
                offer_applied: o.ofType ? { type_slug: o.ofType, discount_value: o.ofVal, buy_qty: o.buyQty, get_qty: o.getQty } : null,
                total_amount:  parseFloat((o.price * qty - disc).toFixed(2)),
            });
        });

        if (!items.length) { toastr.warning('Please add at least one item.'); return; }
        if (!valid)        { toastr.warning('Please select an item for each row.'); return; }

        var foodSub = 0, foodDisc = 0, liquorSub = 0, liquorDisc = 0;
        $('#cbFoodTableBody .cb-food-order-row').each(function () {
            var o = cbReadOffer($(this).find('.cb-food-item-sel option:selected'), cbFoodOfferMap);
            var q = parseInt($(this).find('.cb-food-qty-input').val()) || 0;
            foodSub  += o.price * q;
            foodDisc += cbRowDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });
        $('#cbLiquorTableBody .cb-liquor-order-row').each(function () {
            var o = cbReadOffer($(this).find('.cb-liquor-item-sel option:selected'), cbLiquorOfferMap);
            var q = parseInt($(this).find('.cb-liquor-qty-input').val()) || 0;
            liquorSub  += o.price * q;
            liquorDisc += cbRowDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });
        var subtotal  = foodSub + liquorSub;
        var totalDisc = foodDisc + liquorDisc;
        var gst       = Math.round((foodSub - foodDisc) * GST_RATE * 100) / 100;
        var grand     = parseFloat((subtotal - totalDisc + gst).toFixed(2));

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Placing...');

        $.ajax({
            url:         '{{ url("cancelled-bills") }}/' + cbSessionId + '/reorder',
            type:        'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:          '{{ csrf_token() }}',
                items:           items,
                taxable_amount:  parseFloat(subtotal.toFixed(2)),
                discount_amount: parseFloat(totalDisc.toFixed(2)),
                gst_amount:      gst,
                net_amount:      grand,
            }),
            success: function (res) {
                $btn.prop('disabled', false).html('Place Order');
                if (res.statusCode == 200) {
                    toastr.success(res.message);
                    $('#cbOrderModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('Place Order');
                toastr.error('Something went wrong.');
            }
        });
    });
});
</script>
@endsection
