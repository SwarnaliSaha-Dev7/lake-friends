@extends('base.app')
@section('content')

<div class="row mb-3">
    <div class="col-12">
        <h4 class="fw-bold mb-1">Backdated Order</h4>
        <p class="text-muted small mb-0">Enter all items and place the order in one go. Wallet is deducted from the member's current balance.</p>
    </div>
</div>

{{-- Member + Date + Wallet --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Select Member</label>
                <select id="bd-member-select" class="form-select" style="width:100%;">
                    <option value="">-- Select Club Member --</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->name }} ({{ $m->cardDetails->card_no ?? $m->member_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Order Date</label>
                <input type="date" id="bd-order-date" class="form-control shadow-none"
                       max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Wallet Balance</label>
                <div class="form-control bg-light fw-semibold text-muted" id="bd-wallet-display">—</div>
            </div>
        </div>
    </div>
</div>

{{-- Wallet Insufficient Alert --}}
<div id="bd-wallet-alert" style="display:none;"
     class="alert mb-4 d-flex align-items-center gap-3"
     style="background:#fff7ed;border-left:4px solid #f97316!important;">
    <i class="fa-solid fa-triangle-exclamation fs-5" style="color:#f97316;"></i>
    <div>
        <div class="fw-semibold" style="color:#9a3412;" id="bd-wallet-alert-msg"></div>
        <div class="small text-muted mt-1">Please recharge the member's wallet before placing this order.</div>
    </div>
</div>

{{-- Food Items --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
        <label class="form-label fw-semibold text-dark mb-0">
            <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                <img src="{{ asset('assets/images/d-food-order.svg') }}">
            </span>Food Items
        </label>
    </div>
    <div class="card-body p-4">
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
                <tbody id="bd-food-tbody">
                    <tr id="bd-food-empty-row">
                        <td colspan="6" class="text-center text-muted py-3 small">No food items added.</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-end mt-2">
                <button class="btn btn-info btn-sm" type="button" id="bd-add-food-btn">+ Add Item</button>
            </div>
        </div>
    </div>
</div>

{{-- Liquor Items --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
        <label class="form-label fw-semibold text-dark mb-0">
            <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                <img src="{{ asset('assets/images/d-liquor-order.svg') }}">
            </span>Liquor Items
        </label>
    </div>
    <div class="card-body p-4">
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
                <tbody id="bd-liquor-tbody">
                    <tr id="bd-liquor-empty-row">
                        <td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-end mt-2">
                <button class="btn btn-info btn-sm" type="button" id="bd-add-liquor-btn">+ Add Item</button>
            </div>
        </div>
    </div>
</div>

{{-- Totals + Submit --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row justify-content-end mb-3">
            <div class="col-md-5">
                <div class="p-3 bg-light border rounded-3">
                    <div class="row mb-2 border-bottom p-2">
                        <div class="col-8 text-end text-muted">Subtotal</div>
                        <div class="col-4 text-center fw-semibold" id="bd-subtotal">Rs 0</div>
                    </div>
                    <div class="row mb-2 border-bottom p-2">
                        <div class="col-8 text-end text-muted">GST (10%)</div>
                        <div class="col-4 text-center fw-semibold" id="bd-gst">Rs 0</div>
                    </div>
                    <div class="row mb-2 border-bottom p-2">
                        <div class="col-8 text-end text-warning fw-medium">Offer applied</div>
                        <div class="col-4 text-center text-muted fw-semibold" id="bd-offer-applied">-Rs 0</div>
                    </div>
                    <div class="row mt-3 py-2 bg-dark text-white rounded-3 mx-0">
                        <div class="col-8 text-end">Grand Total</div>
                        <div class="col-4 text-center fw-bold fs-5" id="bd-grand-total">Rs 0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-end">
            <button type="button" class="btn btn-primary px-4 fw-semibold" id="bd-place-order-btn" disabled>
                <i class="fa-solid fa-check me-2"></i>Place Backdated Order
            </button>
        </div>
    </div>
</div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    var bdFoodItems    = [];
    var bdLiquorItems  = [];
    var bdItemsLoaded  = false;
    var bdFoodOfferMap   = {};
    var bdLiquorOfferMap = {};
    var BD_GST_RATE    = 0.10;
    var bdWalletBalance = 0;

    /* ── Select2 ── */
    $('#bd-member-select').select2({ placeholder: 'Search member...', allowClear: true, width: '100%' });

    /* ── Fetch wallet balance when member changes ── */
    $('#bd-member-select').on('change', function () {
        var memberId = $(this).val();
        bdWalletBalance = 0;
        $('#bd-wallet-display').text('—').removeClass('text-danger text-success').addClass('text-muted');
        if (!memberId) { bdCheckBalance(); return; }

        $.get('{{ url("club-member/fetch-wallet-balance") }}/' + memberId, function (res) {
            var bal = res.data && res.data.walletBalance != null ? res.data.walletBalance : res.data;
            bdWalletBalance = parseFloat(bal) || 0;
            $('#bd-wallet-display')
                .text('Rs ' + bdWalletBalance.toFixed(2))
                .removeClass('text-muted text-danger').addClass('text-success');
            bdCheckBalance();
        }).fail(function () {
            $('#bd-wallet-display').text('Error').removeClass('text-muted text-success').addClass('text-danger');
        });
    });

    /* ── Load items ── */
    function bdLoadItems(cb) {
        if (bdItemsLoaded) { if (cb) cb(); return; }
        $.get('{{ route("getOrderItems") }}', function (res) {
            if (res.statusCode == 200) {
                bdFoodItems   = res.foodItems   || [];
                bdLiquorItems = res.liquorItems || [];
                bdFoodItems.forEach(function (it) { bdFoodOfferMap[it.id]   = it.offer || null; });
                bdLiquorItems.forEach(function (it) { bdLiquorOfferMap[it.id] = it.offer || null; });
                bdItemsLoaded = true;
            }
            if (cb) cb();
        });
    }

    /* ── Offer helpers (mirrors app.blade.php) ── */
    function bdRowTotalDiscount(price, ofType, ofVal, qty, buyQty, getQty) {
        qty    = parseInt(qty)    || 1;
        buyQty = parseInt(buyQty) || 1;
        getQty = parseInt(getQty) || 1;
        if (ofType === 'percentage' && ofVal > 0) return price * (ofVal / 100) * qty;
        if (ofType === 'flat'       && ofVal > 0) return Math.min(ofVal, price) * qty;
        if (ofType === 'b1g1') {
            var freeSets = Math.floor(qty / (buyQty + getQty));
            return freeSets * getQty * price;
        }
        return 0;
    }

    function bdOfferBadge(ofType, ofVal, buyQty, getQty) {
        buyQty = parseInt(buyQty) || 1;
        getQty = parseInt(getQty) || 1;
        if (ofType === 'b1g1')
            return '<span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 py-1">Buy ' + buyQty + ' Get ' + getQty + ' Free</span>';
        if (ofType === 'percentage' && ofVal > 0)
            return '<span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">' + ofVal + '% off</span>';
        if (ofType === 'flat' && ofVal > 0)
            return '<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">Rs ' + ofVal + ' off</span>';
        return '<span class="text-muted small">—</span>';
    }

    function bdReadRowOffer($opt, map) {
        var offer = (map && $opt.val()) ? (map[$opt.val()] || null) : null;
        return {
            price:  parseFloat($opt.attr('data-price')) || 0,
            ofType: offer ? (offer.type_slug      || '') : '',
            ofVal:  offer ? (offer.discount_value || 0)  : 0,
            buyQty: offer ? (offer.buy_qty        || 1)  : 1,
            getQty: offer ? (offer.get_qty        || 1)  : 1,
        };
    }

    /* ── Build option HTML ── */
    function bdBuildFoodOptions() {
        var html = '<option value="">-- Select Item --</option>';
        bdFoodItems.forEach(function (it) {
            var pr = (it.food_item_price && it.food_item_price.price) ? it.food_item_price.price : 0;
            html += '<option value="' + it.id + '" data-price="' + pr + '">' + it.name + '</option>';
        });
        return html;
    }

    function bdBuildLiquorOptions() {
        var html = '<option value="">-- Select Item --</option>';
        bdLiquorItems.forEach(function (it) {
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
    function bdBuildFoodRow() {
        return '<tr class="bd-food-order-row">'
            + '<td style="width:40%;"><select class="form-select form-select-sm bd-food-item-sel shadow-none">' + bdBuildFoodOptions() + '</select></td>'
            + '<td><div class="input-group input-group-sm" style="width:100px;">'
            + '<button class="btn btn-outline-warning fw-bold py-0 bd-food-qty-minus" type="button">-</button>'
            + '<input type="text" class="form-control text-center border-warning px-1 bd-food-qty-input" value="1" readonly>'
            + '<button class="btn btn-outline-warning fw-bold py-0 bd-food-qty-plus" type="button">+</button>'
            + '</div></td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning bd-food-unit-price" value="Rs 0" readonly></td>'
            + '<td class="text-nowrap bd-food-offer text-muted small">—</td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning bd-food-total-price" value="Rs 0" readonly></td>'
            + '<td><button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn bd-food-remove-btn" type="button"><small><i class="fa-solid fa-trash"></i></small></button></td>'
            + '</tr>';
    }

    function bdBuildLiquorRow() {
        return '<tr class="bd-liquor-order-row">'
            + '<td style="width:32%;"><select class="form-select form-select-sm bd-liquor-item-sel shadow-none">' + bdBuildLiquorOptions() + '</select></td>'
            + '<td class="bd-liquor-peg-cell" style="min-width:120px;"><span class="text-muted small">Select item first</span>'
            +   '<input type="hidden" class="bd-liquor-volume-ml" value="">'
            +   '<input type="hidden" class="bd-liquor-is-beer" value="">'
            + '</td>'
            + '<td><div class="input-group input-group-sm" style="width:100px;">'
            + '<button class="btn btn-outline-warning fw-bold py-0 bd-liquor-qty-minus" type="button">-</button>'
            + '<input type="text" class="form-control text-center border-warning px-1 bd-liquor-qty-input" value="1" readonly>'
            + '<button class="btn btn-outline-warning fw-bold py-0 bd-liquor-qty-plus" type="button">+</button>'
            + '</div></td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning bd-liquor-unit-price" value="Rs 0" readonly></td>'
            + '<td class="text-nowrap bd-liquor-offer text-muted small">—</td>'
            + '<td><input type="text" class="form-control form-control-sm bg-light border-warning bd-liquor-total-price" value="Rs 0" readonly></td>'
            + '<td><button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn bd-liquor-remove-btn" type="button"><small><i class="fa-solid fa-trash"></i></small></button></td>'
            + '</tr>';
    }

    /* ── Add row buttons ── */
    $('#bd-add-food-btn').on('click', function () {
        bdLoadItems(function () {
            $('#bd-food-empty-row').remove();
            var $row = $(bdBuildFoodRow());
            $('#bd-food-tbody').append($row);
            $row.find('.bd-food-item-sel').select2({ dropdownParent: $('body'), placeholder: 'Search food item...', allowClear: true, width: '100%' });
            bdRecalc();
        });
    });

    $('#bd-add-liquor-btn').on('click', function () {
        bdLoadItems(function () {
            $('#bd-liquor-empty-row').remove();
            var $row = $(bdBuildLiquorRow());
            $('#bd-liquor-tbody').append($row);
            $row.find('.bd-liquor-item-sel').select2({ dropdownParent: $('body'), placeholder: 'Search liquor item...', allowClear: true, width: '100%' });
            bdRecalc();
        });
    });

    /* ── Food item change ── */
    $(document).on('change', '.bd-food-item-sel', function () {
        var o    = bdReadRowOffer($(this).find('option:selected'), bdFoodOfferMap);
        var $row = $(this).closest('tr');
        var qty  = parseInt($row.find('.bd-food-qty-input').val()) || 1;
        var disc = bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.bd-food-unit-price').val('Rs ' + o.price.toFixed(2));
        $row.find('.bd-food-offer').html(bdOfferBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
        $row.find('.bd-food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        bdRecalc();
    });

    /* ── Food qty +/- ── */
    $(document).on('click', '.bd-food-qty-plus', function () {
        var $row = $(this).closest('tr');
        var $inp = $row.find('.bd-food-qty-input');
        $inp.val(parseInt($inp.val()) + 1);
        bdUpdateFoodRowTotal($row);
    });
    $(document).on('click', '.bd-food-qty-minus', function () {
        var $row = $(this).closest('tr');
        var $inp = $row.find('.bd-food-qty-input');
        $inp.val(Math.max(1, parseInt($inp.val()) - 1));
        bdUpdateFoodRowTotal($row);
    });
    function bdUpdateFoodRowTotal($row) {
        var o    = bdReadRowOffer($row.find('.bd-food-item-sel option:selected'), bdFoodOfferMap);
        var qty  = parseInt($row.find('.bd-food-qty-input').val()) || 1;
        var disc = bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.bd-food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        bdRecalc();
    }

    /* ── Food row remove ── */
    $(document).on('click', '.bd-food-remove-btn', function () {
        $(this).closest('tr').remove();
        if (!$('#bd-food-tbody .bd-food-order-row').length) {
            $('#bd-food-tbody').html('<tr id="bd-food-empty-row"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
        }
        bdRecalc();
    });

    /* ── Liquor item change ── */
    $(document).on('change', '.bd-liquor-item-sel', function () {
        var $opt     = $(this).find('option:selected');
        var $row     = $(this).closest('tr');
        var isBeer   = $opt.attr('data-is-beer') == '1';
        var volumeMl = parseInt($opt.attr('data-volume-ml')) || 0;
        var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
        var itemId   = $opt.val();
        var $cell    = $row.find('.bd-liquor-peg-cell');

        if (!itemId) {
            $cell.html('<span class="text-muted small">Select item first</span>'
                + '<input type="hidden" class="bd-liquor-volume-ml" value="">'
                + '<input type="hidden" class="bd-liquor-is-beer" value="">');
            $row.find('.bd-liquor-unit-price').val('Rs 0');
            $row.find('.bd-liquor-total-price').val('Rs 0');
            bdRecalc();
            return;
        }

        var stockLabel = barStock > 0
            ? '<span class="text-success small ms-1">Stock: ' + barStock + (isBeer ? ' BTL' : 'ml') + '</span>'
            : '<span class="text-danger small ms-1">Out of stock</span>';

        if (isBeer) {
            $cell.html(
                '<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">1 BTL</span>'
                + stockLabel
                + '<input type="hidden" class="bd-liquor-volume-ml" value="">'
                + '<input type="hidden" class="bd-liquor-is-beer" value="1">'
            );
        } else {
            $cell.html(
                '<span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-2 py-1">' + volumeMl + 'ml</span>'
                + stockLabel
                + '<input type="hidden" class="bd-liquor-volume-ml" value="' + volumeMl + '">'
                + '<input type="hidden" class="bd-liquor-is-beer" value="0">'
            );
        }

        bdUpdateLiquorRowTotal($row);
    });

    /* ── Liquor qty +/- ── */
    $(document).on('click', '.bd-liquor-qty-plus', function () {
        var $row     = $(this).closest('tr');
        var $inp     = $row.find('.bd-liquor-qty-input');
        var $opt     = $row.find('.bd-liquor-item-sel option:selected');
        var isBeer   = $row.find('.bd-liquor-is-beer').val() === '1';
        var volumeMl = parseInt($row.find('.bd-liquor-volume-ml').val()) || 0;
        var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
        var curQty   = parseInt($inp.val()) || 1;
        var maxQty   = isBeer ? barStock : (volumeMl > 0 ? Math.floor(barStock / volumeMl) : 0);

        if (maxQty > 0 && curQty >= maxQty) {
            toastr.warning('Stock limit reached (' + maxQty + (isBeer ? ' BTL' : ' servings') + ' available).');
            return;
        }
        $inp.val(curQty + 1);
        bdUpdateLiquorRowTotal($row);
    });
    $(document).on('click', '.bd-liquor-qty-minus', function () {
        var $row = $(this).closest('tr');
        var $inp = $row.find('.bd-liquor-qty-input');
        $inp.val(Math.max(1, parseInt($inp.val()) - 1));
        bdUpdateLiquorRowTotal($row);
    });
    function bdUpdateLiquorRowTotal($row) {
        var o    = bdReadRowOffer($row.find('.bd-liquor-item-sel option:selected'), bdLiquorOfferMap);
        var qty  = parseInt($row.find('.bd-liquor-qty-input').val()) || 1;
        var disc = bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        $row.find('.bd-liquor-unit-price').val('Rs ' + o.price.toFixed(2));
        $row.find('.bd-liquor-offer').html(bdOfferBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
        $row.find('.bd-liquor-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
        bdRecalc();
    }

    /* ── Liquor row remove ── */
    $(document).on('click', '.bd-liquor-remove-btn', function () {
        $(this).closest('tr').remove();
        if (!$('#bd-liquor-tbody .bd-liquor-order-row').length) {
            $('#bd-liquor-tbody').html('<tr id="bd-liquor-empty-row"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
        }
        bdRecalc();
    });

    /* ── Recalc totals (GST on food only, same as existing order flow) ── */
    function bdRecalc() {
        var foodSubtotal = 0, foodDiscount = 0;
        var liquorSubtotal = 0, liquorDiscount = 0;

        $('#bd-food-tbody .bd-food-order-row').each(function () {
            var o   = bdReadRowOffer($(this).find('.bd-food-item-sel option:selected'), bdFoodOfferMap);
            var qty = parseInt($(this).find('.bd-food-qty-input').val()) || 0;
            foodSubtotal += o.price * qty;
            foodDiscount += bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        });
        $('#bd-liquor-tbody .bd-liquor-order-row').each(function () {
            var o   = bdReadRowOffer($(this).find('.bd-liquor-item-sel option:selected'), bdLiquorOfferMap);
            var qty = parseInt($(this).find('.bd-liquor-qty-input').val()) || 0;
            liquorSubtotal += o.price * qty;
            liquorDiscount += bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
        });

        var totalSubtotal    = foodSubtotal + liquorSubtotal;
        var totalDiscount    = foodDiscount + liquorDiscount;
        var foodAfterDiscount = foodSubtotal - foodDiscount;
        var gst              = Math.round(foodAfterDiscount * BD_GST_RATE * 100) / 100;
        var grandTotal       = (totalSubtotal - totalDiscount) + gst;

        $('#bd-subtotal').text('Rs ' + totalSubtotal.toFixed(2));
        $('#bd-gst').text('Rs ' + gst.toFixed(2));
        $('#bd-offer-applied').text('-Rs ' + totalDiscount.toFixed(2));
        $('#bd-grand-total').text('Rs ' + grandTotal.toFixed(2));

        bdCheckBalance();
    }

    function bdCheckBalance() {
        var grandTotal = parseFloat($('#bd-grand-total').text().replace('Rs ', '')) || 0;
        var hasItems   = ($('#bd-food-tbody .bd-food-order-row').length + $('#bd-liquor-tbody .bd-liquor-order-row').length) > 0;
        var memberId   = $('#bd-member-select').val();

        if (memberId && grandTotal > 0 && bdWalletBalance < grandTotal) {
            $('#bd-wallet-alert-msg').text(
                'Insufficient balance. Available: Rs ' + bdWalletBalance.toFixed(2) +
                ', Required: Rs ' + grandTotal.toFixed(2)
            );
            $('#bd-wallet-alert').show();
            $('#bd-place-order-btn').prop('disabled', true);
        } else {
            $('#bd-wallet-alert').hide();
            $('#bd-place-order-btn').prop('disabled', !(memberId && hasItems && grandTotal > 0));
        }
    }

    /* ── Place Order ── */
    $('#bd-place-order-btn').on('click', function () {
        var memberId  = $('#bd-member-select').val();
        var orderDate = $('#bd-order-date').val();
        if (!memberId)  { toastr.warning('Please select a member.'); return; }
        if (!orderDate) { toastr.warning('Please select an order date.'); return; }

        var items = [];
        var valid = true;

        // Collect food rows
        $('#bd-food-tbody .bd-food-order-row').each(function () {
            var $opt   = $(this).find('.bd-food-item-sel option:selected');
            var itemId = $opt.val();
            if (!itemId) { valid = false; return false; }
            var o    = bdReadRowOffer($opt, bdFoodOfferMap);
            var qty  = parseInt($(this).find('.bd-food-qty-input').val()) || 1;
            var disc = bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            items.push({
                food_item_id:  itemId,
                quantity:      qty,
                unit:          'plate',
                unit_price:    o.price,
                offer_applied: o.ofType ? { type_slug: o.ofType, discount_value: o.ofVal, buy_qty: o.buyQty, get_qty: o.getQty } : null,
                total_amount:  parseFloat((o.price * qty - disc).toFixed(2)),
            });
        });

        if (!valid) { toastr.warning('Please select an item for each row.'); return; }

        // Collect liquor rows
        $('#bd-liquor-tbody .bd-liquor-order-row').each(function () {
            var $opt       = $(this).find('.bd-liquor-item-sel option:selected');
            var itemId     = $opt.val();
            if (!itemId) { valid = false; return false; }
            var foodItemId = $opt.attr('data-food-item-id') || itemId;
            var isBeer     = $(this).find('.bd-liquor-is-beer').val() === '1';
            var volumeMl   = parseInt($(this).find('.bd-liquor-volume-ml').val()) || 0;
            var o          = bdReadRowOffer($opt, bdLiquorOfferMap);
            var qty        = parseInt($(this).find('.bd-liquor-qty-input').val()) || 1;
            var disc       = bdRowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            var deductQty  = isBeer ? qty : qty * volumeMl;
            items.push({
                food_item_id:  foodItemId,
                quantity:      qty,
                unit:          isBeer ? 'btl' : 'ml',
                volume_ml:     isBeer ? null : volumeMl,
                deduct_qty:    deductQty,
                unit_price:    o.price,
                offer_applied: o.ofType ? { type_slug: o.ofType, discount_value: o.ofVal, buy_qty: o.buyQty, get_qty: o.getQty } : null,
                total_amount:  parseFloat((o.price * qty - disc).toFixed(2)),
            });
        });

        if (!valid) { toastr.warning('Please select an item for each row.'); return; }
        if (!items.length) { toastr.warning('Please add at least one item.'); return; }

        // Recompute totals for submission
        var foodSubtotal = 0, foodDiscount = 0, liquorSubtotal = 0, liquorDiscount = 0;
        $('#bd-food-tbody .bd-food-order-row').each(function () {
            var o = bdReadRowOffer($(this).find('.bd-food-item-sel option:selected'), bdFoodOfferMap);
            var q = parseInt($(this).find('.bd-food-qty-input').val()) || 0;
            foodSubtotal += o.price * q;
            foodDiscount += bdRowTotalDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });
        $('#bd-liquor-tbody .bd-liquor-order-row').each(function () {
            var o = bdReadRowOffer($(this).find('.bd-liquor-item-sel option:selected'), bdLiquorOfferMap);
            var q = parseInt($(this).find('.bd-liquor-qty-input').val()) || 0;
            liquorSubtotal += o.price * q;
            liquorDiscount += bdRowTotalDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
        });

        var totalSubtotal    = foodSubtotal + liquorSubtotal;
        var totalDiscount    = foodDiscount + liquorDiscount;
        var foodAfterDiscount = foodSubtotal - foodDiscount;
        var gst              = Math.round(foodAfterDiscount * BD_GST_RATE * 100) / 100;
        var grandTotal       = (totalSubtotal - totalDiscount) + gst;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Placing Order...');

        $.ajax({
            url:         '{{ route("backdated-orders.store") }}',
            type:        'POST',
            headers:     { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data:        JSON.stringify({
                member_id:       memberId,
                order_date:      orderDate,
                items:           items,
                taxable_amount:  parseFloat((totalSubtotal - totalDiscount).toFixed(2)),
                discount_amount: parseFloat(totalDiscount.toFixed(2)),
                gst_amount:      gst,
                net_amount:      parseFloat(grandTotal.toFixed(2)),
            }),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success('Order placed! Session: ' + res.session_no + ' | Order: ' + res.order_no + ' | Rs ' + res.net_amount);
                    bdReset();
                } else if (res.insufficient) {
                    $('#bd-wallet-alert-msg').text(res.message);
                    $('#bd-wallet-alert').show();
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-2"></i>Place Backdated Order');
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-2"></i>Place Backdated Order');
                }
            },
            error: function () {
                toastr.error('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-2"></i>Place Backdated Order');
            }
        });
    });

    /* ── Reset form ── */
    function bdReset() {
        $('#bd-member-select').val(null).trigger('change');
        $('#bd-order-date').val('{{ date("Y-m-d") }}');
        bdWalletBalance = 0;
        $('#bd-wallet-display').text('—').removeClass('text-success text-danger').addClass('text-muted');
        $('#bd-wallet-alert').hide();
        $('#bd-food-tbody').html('<tr id="bd-food-empty-row"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
        $('#bd-liquor-tbody').html('<tr id="bd-liquor-empty-row"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
        $('#bd-subtotal').text('Rs 0');
        $('#bd-gst').text('Rs 0');
        $('#bd-offer-applied').text('-Rs 0');
        $('#bd-grand-total').text('Rs 0');
        $('#bd-place-order-btn').prop('disabled', true).html('<i class="fa-solid fa-check me-2"></i>Place Backdated Order');
    }

});
</script>
@endsection
