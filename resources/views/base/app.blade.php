<!DOCTYPE html>
<html lang="en">

<head>
    @include('base.head')
</head>

<body>
    <section class="dashboard-wrapper">

        {{-- Left Panel --}}
        @include('partials.left-panel')
        <div class="right-panel">

            {{-- Header --}}
            @include('partials.header')

            {{-- Page Content --}}
            <div class="right-body pb-4 pe-3 ps-lg-5 ps-3">
                @yield('content')
            </div>
        </div>
    </section>

    @yield('modalComponent')
    <!-- card entry swipe Modal start -->
    <div class="modal fade" id="cardentryswipe" tabindex="-1" aria-labelledby="cardentryswipeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="cardentryswipeModalLabel">Gate Entry Logs</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="swipe-animation">
                        <div class="credit-card">
                            <div class="scc-tripe"></div>
                        </div>
                        <div class="swiper-top"></div>
                        <div class="swiper-bottom">
                            <div class="light-indicator"></div>
                        </div>
                    </div>

                    <div id="cardLoader" class="text-center py-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted small">Processing card...</p>
                    </div>

                    <form action="">
                        <input type="text" class="form-control py-2 shadow-none" id="cardInput" style="opacity: 0;">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- card entry swipe Modal end -->

    <!-- ===================== Member Info Modal ===================== -->
    <div class="modal fade" id="cardentry" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">

                <!-- Gradient Header -->
                <div class="p-4 text-white position-relative" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    <div class="d-flex align-items-center gap-3">
                        <div id="cardMemberAvatar"
                            class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0"
                            style="width:56px;height:56px;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.4);">
                            ?
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-semibold fs-6 text-truncate lh-sm" id="cardMemberName">—</div>
                            <div class="small opacity-75 text-truncate" id="cardMemberClubName">—</div>
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                <span class="badge rounded-pill px-2 py-1 border border-white border-opacity-50 small" id="cardStatusBadge" style="font-size:0.68rem;background:rgba(255,255,255,0.15);">—</span>
                                <span class="badge rounded-pill px-2 py-1 border border-white border-opacity-50 small" id="memberStatusBadge" style="font-size:0.68rem;background:rgba(255,255,255,0.15);">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="rounded-3 p-2" style="background:#f8f9fa;">
                                <div class="text-muted mb-1" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:.04em;">Member ID</div>
                                <div class="fw-semibold small text-truncate" id="cardMemberCode">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-2" style="background:#f8f9fa;">
                                <div class="text-muted mb-1" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:.04em;">Card No</div>
                                <div class="fw-semibold small text-truncate" id="cardMemberCardNo">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-2" style="background:#f8f9fa;">
                                <div class="text-muted mb-1" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:.04em;">Active Plan</div>
                                <div class="fw-semibold small text-truncate" id="cardMemberPlan">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-2" style="background:#f8f9fa;">
                                <div class="text-muted mb-1" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:.04em;">Plan Expiry</div>
                                <div class="fw-semibold small text-truncate" id="cardMemberPlanExpiry">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Wallet -->
                    <div class="rounded-3 px-3 py-2 d-flex align-items-center justify-content-between mb-3"
                        style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #a7f3d0;">
                        <div>
                            <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:.04em;">Wallet Balance</div>
                            <div class="fw-bold fs-5 text-success lh-sm" id="cardMemberWallet">Rs.0.00</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success"
                            style="width:40px;height:40px;background:rgba(16,185,129,0.12);">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn flex-fill py-2 fw-medium" id="walletRechargeBtn"
                            style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:10px;font-size:0.82rem;">
                            <i class="fa-solid fa-wallet d-block mb-1 fs-6"></i>Wallet Recharge
                        </button>
                        <button type="button" class="btn flex-fill py-2 fw-medium" id="renewalBtn"
                            style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;border-radius:10px;font-size:0.82rem;">
                            <i class="fa-solid fa-rotate-right d-block mb-1 fs-6"></i>Renewal
                        </button>
                        <button type="button" class="btn flex-fill py-2 fw-medium" id="createOrderBtn"
                            style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:10px;font-size:0.82rem;">
                            <i class="fa-solid fa-cart-plus d-block mb-1 fs-6"></i>Create Order
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Member Info Modal end -->

   
    <!-- create order modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="createOrderModalLabel">Create Order</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">

                    <!-- Member & Order Info -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="fw-bold mb-0" id="orderMemberName">—</h5>
                            <p class="text-muted small mb-0" id="orderMemberMeta">—</p>
                        </div>
                        <div class="text-end text-muted small">
                            <div>Order Date: <strong id="orderDate">—</strong>&nbsp; Time: <strong id="orderTime">—</strong></div>
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
                                        <tbody id="foodTableBody">
                                            <tr id="foodEmptyRow">
                                                <td colspan="6" class="text-center text-muted py-3 small">No food items added.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="text-end mt-2">
                                        <button class="btn btn-info btn-sm add-food-item" type="button">+ Add Item</button>
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
                                        <tbody id="liquorTableBody">
                                            <tr id="liquorEmptyRow">
                                                <td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="text-end mt-2">
                                        <button class="btn btn-info btn-sm add-liquor-item" type="button">+ Add Item</button>
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
                                <div class="col-4 text-center fw-semibold" id="orderSubtotal">Rs 0</div>
                            </div>
                            <div class="row mb-2 border-bottom p-2">
                                <div class="col-8 text-end text-muted">GST (10%)</div>
                                <div class="col-4 text-center fw-semibold" id="orderGst">Rs 0</div>
                            </div>
                            <div class="row mb-2 border-bottom p-2">
                                <div class="col-8 text-end text-warning fw-medium">Offer applied</div>
                                <div class="col-4 text-center text-muted fw-semibold" id="orderOfferApplied">-Rs 0</div>
                            </div>
                            <div class="row mt-3 py-2 bg-dark text-white rounded-3 mx-0">
                                <div class="col-8 text-end">Grand Total</div>
                                <div class="col-4 text-center fw-bold fs-5" id="orderGrandTotal">Rs 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-primary" id="payWithWalletBtn">wallet</button>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="placeOrderBtn">Place Order</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- create modal end -->

    @include('base.scripts')
    @yield('customJS')
    <script>
    $(document).ready(function () {

        var allFoodItems   = [];
        var allLiquorItems = [];
        var itemsLoaded    = false;
        var GST_RATE       = 0.10;
        var foodOfferMap   = {};   // item id → offer object
        var liquorOfferMap = {};   // item id → offer object

        /* ---- Build option HTML ---- */
        function buildFoodOptions(items) {
            var html = '<option value="">-- Select Item --</option>';
            for (var i = 0; i < items.length; i++) {
                var it = items[i];
                var pr = (it.food_item_price && it.food_item_price.price) ? it.food_item_price.price : 0;
                foodOfferMap[it.id] = it.offer || null;
                html += '<option value="' + it.id + '"'
                    + ' data-price="' + pr + '">'
                    + it.name + '</option>';
            }
            return html;
        }

        function buildLiquorOptions(items) {
            var html = '<option value="">-- Select Item --</option>';
            for (var i = 0; i < items.length; i++) {
                var it = items[i];
                liquorOfferMap[it.id] = it.offer || null;
                html += '<option value="' + it.id + '"'
                    + ' data-food-item-id="' + it.food_item_id + '"'
                    + ' data-price="' + it.price + '"'
                    + ' data-is-beer="' + (it.is_beer ? '1' : '0') + '"'
                    + ' data-volume-ml="' + (it.volume_ml || 0) + '"'
                    + ' data-bar-stock="' + (it.bar_stock || 0) + '">'
                    + it.name + '</option>';
            }
            return html;
        }

        /* ---- Build row HTML ---- */
        function buildFoodRowHtml() {
            return '<tr class="food-order-row">'
                + '<td style="width:40%;"><select class="form-select form-select-sm food-item-sel shadow-none">'
                + buildFoodOptions(allFoodItems) + '</select></td>'
                + '<td><div class="input-group input-group-sm" style="width:100px;">'
                + '<button class="btn btn-outline-warning fw-bold py-0 food-qty-minus" type="button">-</button>'
                + '<input type="text" class="form-control text-center border-warning px-1 food-qty-input" value="1" readonly>'
                + '<button class="btn btn-outline-warning fw-bold py-0 food-qty-plus" type="button">+</button>'
                + '</div></td>'
                + '<td><input type="text" class="form-control form-control-sm bg-light border-warning food-unit-price" value="Rs 0" readonly></td>'
                + '<td class="text-nowrap food-offer text-muted small">—</td>'
                + '<td><input type="text" class="form-control form-control-sm bg-light border-warning food-total-price" value="Rs 0" readonly></td>'
                + '<td class="text-nowrap"><button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-food-row" title="Delete"><small><i class="fa-solid fa-trash"></i></small></button></td>'
                + '</tr>';
        }

        function buildLiquorRowHtml() {
            return '<tr class="liquor-order-row">'
                + '<td style="width:32%;">'
                +   '<select class="form-select form-select-sm liquor-item-sel shadow-none">' + buildLiquorOptions(allLiquorItems) + '</select>'
                + '</td>'
                + '<td class="liquor-peg-cell" style="min-width:140px;">'
                +   '<span class="text-muted small">Select item first</span>'
                +   '<input type="hidden" class="liquor-volume-ml" value="">'
                +   '<input type="hidden" class="liquor-is-beer" value="">'
                + '</td>'
                + '<td><div class="input-group input-group-sm" style="width:100px;">'
                + '<button class="btn btn-outline-warning fw-bold py-0 liquor-qty-minus" type="button">-</button>'
                + '<input type="text" class="form-control text-center border-warning px-1 liquor-qty-input" value="1" readonly>'
                + '<button class="btn btn-outline-warning fw-bold py-0 liquor-qty-plus" type="button">+</button>'
                + '</div></td>'
                + '<td><input type="text" class="form-control form-control-sm bg-light border-warning liquor-unit-price" value="Rs 0" readonly></td>'
                + '<td class="text-nowrap liquor-offer text-muted small">—</td>'
                + '<td><input type="text" class="form-control form-control-sm bg-light border-warning liquor-total-price" value="Rs 0" readonly></td>'
                + '<td class="text-nowrap"><button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-liquor-row" title="Delete"><small><i class="fa-solid fa-trash"></i></small></button></td>'
                + '</tr>';
        }

        /* ---- Add row helpers (append + init Select2) ---- */
        function addFoodRow() {
            $('#foodEmptyRow').remove();
            var $row = $(buildFoodRowHtml());
            $('#foodTableBody').append($row);
            $row.find('.food-item-sel').select2({
                dropdownParent: $('#createOrderModal'),
                placeholder:    'Search food item...',
                allowClear:     true,
                width:          '100%',
            });
            recalc();
        }

        function addLiquorRow() {
            $('#liquorEmptyRow').remove();
            var $row = $(buildLiquorRowHtml());
            $('#liquorTableBody').append($row);
            $row.find('.liquor-item-sel').select2({
                dropdownParent: $('#createOrderModal'),
                placeholder:    'Search liquor item...',
                allowClear:     true,
                width:          '100%',
            });
            recalc();
        }

        /* ---- Liquor item selected → update volume cell ---- */
        $(document).on('change', '.liquor-item-sel', function () {
            var $opt     = $(this).find('option:selected');
            var $row     = $(this).closest('tr');
            var isBeer   = $opt.attr('data-is-beer') == '1';
            var volumeMl = parseInt($opt.attr('data-volume-ml')) || 0;
            var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
            var itemId   = $opt.val();
            var $cell    = $row.find('.liquor-peg-cell');

            if (!itemId) {
                $cell.html('<span class="text-muted small">Select item first</span>'
                    + '<input type="hidden" class="liquor-volume-ml" value="">'
                    + '<input type="hidden" class="liquor-is-beer" value="">');
                $row.find('.liquor-unit-price').val('Rs 0');
                $row.find('.liquor-total-price').val('Rs 0');
                recalc();
                return;
            }

            if (isBeer) {
                var stockLabel = barStock > 0
                    ? '<span class="text-success small ms-1">Stock: ' + barStock + ' BTL</span>'
                    : '<span class="text-danger small ms-1">Out of stock</span>';
                $cell.html(
                    '<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">1 BTL</span>'
                    + stockLabel
                    + '<input type="hidden" class="liquor-volume-ml" value="">'
                    + '<input type="hidden" class="liquor-is-beer" value="1">'
                );
            } else {
                var stockLabel2 = barStock > 0
                    ? '<span class="text-success small ms-1">Stock: ' + barStock + 'ml</span>'
                    : '<span class="text-danger small ms-1">Out of stock</span>';
                $cell.html(
                    '<span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-2 py-1">' + volumeMl + 'ml</span>'
                    + stockLabel2
                    + '<input type="hidden" class="liquor-volume-ml" value="' + volumeMl + '">'
                    + '<input type="hidden" class="liquor-is-beer" value="0">'
                );
            }

            updateLiquorRowTotal($row);
        });

        /* ---- Open Create Order ---- */
        $('#createOrderBtn').on('click', function () {
            var now = new Date();
            var dd  = String(now.getDate()).padStart(2, '0');
            var mm  = String(now.getMonth() + 1).padStart(2, '0');
            var yy  = now.getFullYear();
            var hh  = String(now.getHours()).padStart(2, '0');
            var mi  = String(now.getMinutes()).padStart(2, '0');

            $('#orderMemberName').text($('#cardMemberName').text());
            $('#orderMemberMeta').text($('#cardMemberPlan').text() + ' member | Member\'s Id: ' + $('#cardMemberCode').text());
            $('#orderDate').text(dd + '-' + mm + '-' + yy);
            $('#orderTime').text(hh + ':' + mi);

            $('#foodTableBody').html('<tr id="foodEmptyRow"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
            $('#liquorTableBody').html('<tr id="liquorEmptyRow"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
            recalc();

            $('#createOrderModal').modal('show');

            if (!itemsLoaded) {
                $.get('{{ route("getOrderItems") }}', function (res) {
                    if (res.statusCode == 200) {
                        allFoodItems   = res.foodItems;
                        allLiquorItems = res.liquorItems;
                        itemsLoaded    = true;
                    }
                    addFoodRow();
                });
            } else {
                addFoodRow();
            }
        });

        /* ---- Add food / liquor rows via button ---- */
        $(document).on('click', '.add-food-item',   function () { addFoodRow();   });
        $(document).on('click', '.add-liquor-item', function () { addLiquorRow(); });

        /* ---- Helpers ---- */
        function rowTotalDiscount(price, ofType, ofVal, qty, buyQty, getQty) {
            qty    = parseInt(qty)    || 1;
            buyQty = parseInt(buyQty) || 1;
            getQty = parseInt(getQty) || 1;
            if (ofType === 'percentage' && ofVal > 0)
                return price * (ofVal / 100) * qty;
            if (ofType === 'flat' && ofVal > 0)
                return Math.min(ofVal, price) * qty;
            if (ofType === 'b1g1') {
                var freeSets = Math.floor(qty / (buyQty + getQty));
                return freeSets * getQty * price;
            }
            return 0;
        }

        function offerBadge(ofType, ofVal, buyQty, getQty) {
            buyQty = parseInt(buyQty) || 1;
            getQty = parseInt(getQty) || 1;
            if (ofType === 'b1g1')
                return '<span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 py-1"'
                     + ' title="Buy ' + buyQty + ' Get ' + getQty + ' Free">'
                     + 'Buy ' + buyQty + ' Get ' + getQty + ' Free</span>';
            if (ofType === 'percentage' && ofVal > 0)
                return '<span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">'
                     + ofVal + '% off</span>';
            if (ofType === 'flat' && ofVal > 0)
                return '<span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1">'
                     + 'Rs ' + ofVal + ' off</span>';
            return '<span class="text-muted small">—</span>';
        }

        function readRowOffer($opt, map) {
            var offer  = (map && $opt.val()) ? (map[$opt.val()] || null) : null;
            return {
                price:  parseFloat($opt.attr('data-price')) || 0,
                ofType: offer ? (offer.type_slug      || '') : '',
                ofVal:  offer ? (offer.discount_value || 0)  : 0,
                buyQty: offer ? (offer.buy_qty        || 1)  : 1,
                getQty: offer ? (offer.get_qty        || 1)  : 1,
            };
        }

        /* ---- Food item select change ---- */
        $(document).on('change', '.food-item-sel', function () {
            var o    = readRowOffer($(this).find('option:selected'), foodOfferMap);
            var $row = $(this).closest('tr');
            var qty  = parseInt($row.find('.food-qty-input').val()) || 1;
            var disc = rowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            $row.find('.food-unit-price').val('Rs ' + o.price.toFixed(2));
            $row.find('.food-offer').html(offerBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
            $row.find('.food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
            recalc();
        });

        /* ---- Qty +/- food ---- */
        $(document).on('click', '.food-qty-plus', function () {
            var $row = $(this).closest('tr');
            var $inp = $row.find('.food-qty-input');
            $inp.val(parseInt($inp.val()) + 1);
            updateFoodRowTotal($row);
        });
        $(document).on('click', '.food-qty-minus', function () {
            var $row = $(this).closest('tr');
            var $inp = $row.find('.food-qty-input');
            $inp.val(Math.max(1, parseInt($inp.val()) - 1));
            updateFoodRowTotal($row);
        });
        function updateFoodRowTotal($row) {
            var o    = readRowOffer($row.find('.food-item-sel option:selected'), foodOfferMap);
            var qty  = parseInt($row.find('.food-qty-input').val()) || 1;
            var disc = rowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            $row.find('.food-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
            recalc();
        }

        /* ---- Qty +/- liquor ---- */
        $(document).on('click', '.liquor-qty-plus', function () {
            var $row     = $(this).closest('tr');
            var $inp     = $row.find('.liquor-qty-input');
            var $opt     = $row.find('.liquor-item-sel option:selected');
            var isBeer   = $row.find('.liquor-is-beer').val() === '1';
            var volumeMl = parseInt($row.find('.liquor-volume-ml').val()) || 0;
            var barStock = parseInt($opt.attr('data-bar-stock')) || 0;
            var curQty   = parseInt($inp.val()) || 1;
            var maxQty   = isBeer ? barStock : (volumeMl > 0 ? Math.floor(barStock / volumeMl) : 0);

            if (maxQty > 0 && curQty >= maxQty) {
                toastr.warning('Stock limit reached (' + maxQty + (isBeer ? ' BTL' : ' servings') + ' available).');
                return;
            }
            $inp.val(curQty + 1);
            updateLiquorRowTotal($row);
        });
        $(document).on('click', '.liquor-qty-minus', function () {
            var $row = $(this).closest('tr');
            var $inp = $row.find('.liquor-qty-input');
            $inp.val(Math.max(1, parseInt($inp.val()) - 1));
            updateLiquorRowTotal($row);
        });
        function updateLiquorRowTotal($row) {
            var o    = readRowOffer($row.find('.liquor-item-sel option:selected'), liquorOfferMap);
            var qty  = parseInt($row.find('.liquor-qty-input').val()) || 1;
            var disc = rowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            $row.find('.liquor-unit-price').val('Rs ' + o.price.toFixed(2));
            $row.find('.liquor-offer').html(offerBadge(o.ofType, o.ofVal, o.buyQty, o.getQty));
            $row.find('.liquor-total-price').val('Rs ' + (o.price * qty - disc).toFixed(2));
            recalc();
        }

        /* ---- Delete rows ---- */
        $(document).on('click', '.delete-food-row', function () {
            $(this).closest('tr').remove();
            if ($('#foodTableBody tr').length === 0) {
                $('#foodTableBody').html('<tr id="foodEmptyRow"><td colspan="6" class="text-center text-muted py-3 small">No food items added.</td></tr>');
            }
            recalc();
        });
        $(document).on('click', '.delete-liquor-row', function () {
            $(this).closest('tr').remove();
            if ($('#liquorTableBody tr').length === 0) {
                $('#liquorTableBody').html('<tr id="liquorEmptyRow"><td colspan="7" class="text-center text-muted py-3 small">No liquor items added.</td></tr>');
            }
            recalc();
        });

        /* ---- Recalculate totals (GST on food only) ---- */
        function recalc() {
            var foodSubtotal = 0, foodDiscount = 0;
            var liquorSubtotal = 0, liquorDiscount = 0;

            $('#foodTableBody .food-order-row').each(function () {
                var o   = readRowOffer($(this).find('.food-item-sel option:selected'), foodOfferMap);
                var qty = parseInt($(this).find('.food-qty-input').val()) || 0;
                foodSubtotal += o.price * qty;
                foodDiscount += rowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            });
            $('#liquorTableBody .liquor-order-row').each(function () {
                var o   = readRowOffer($(this).find('.liquor-item-sel option:selected'), liquorOfferMap);
                var qty = parseInt($(this).find('.liquor-qty-input').val()) || 0;
                liquorSubtotal += o.price * qty;
                liquorDiscount += rowTotalDiscount(o.price, o.ofType, o.ofVal, qty, o.buyQty, o.getQty);
            });

            var totalSubtotal    = foodSubtotal + liquorSubtotal;
            var totalDiscount    = foodDiscount + liquorDiscount;
            var foodAfterDiscount = foodSubtotal - foodDiscount;
            var gst              = Math.round(foodAfterDiscount * GST_RATE * 100) / 100;
            var grand            = (totalSubtotal - totalDiscount) + gst;

            $('#orderSubtotal').text('Rs ' + totalSubtotal.toFixed(2));
            $('#orderGst').text('Rs ' + gst.toFixed(2));
            $('#orderOfferApplied').text('-Rs ' + totalDiscount.toFixed(2));
            $('#orderGrandTotal').text('Rs ' + grand.toFixed(2));
        }

        /* ---- Place Order ---- */
        $('#placeOrderBtn, #payWithWalletBtn').on('click', function () {
            var items = [];
            var valid = true;

            // Collect food rows
            $('#foodTableBody .food-order-row').each(function () {
                var $opt   = $(this).find('.food-item-sel option:selected');
                var itemId = $opt.val();
                if (!itemId) { valid = false; return false; }
                var fo     = readRowOffer($opt, foodOfferMap);
                var qty    = parseInt($(this).find('.food-qty-input').val()) || 1;
                var disc   = rowTotalDiscount(fo.price, fo.ofType, fo.ofVal, qty, fo.buyQty, fo.getQty);
                items.push({
                    food_item_id:  itemId,
                    quantity:      qty,
                    unit:          'plate',
                    unit_price:    fo.price,
                    offer_applied: fo.ofType ? { type_slug: fo.ofType, discount_value: fo.ofVal, buy_qty: fo.buyQty, get_qty: fo.getQty } : null,
                    total_amount:  parseFloat((fo.price * qty - disc).toFixed(2)),
                });
            });

            // Collect liquor rows
            $('#liquorTableBody .liquor-order-row').each(function () {
                var $opt       = $(this).find('.liquor-item-sel option:selected');
                var itemId     = $opt.val();
                if (!itemId) { valid = false; return false; }

                var foodItemId = $opt.attr('data-food-item-id') || itemId;
                var isBeer     = $(this).find('.liquor-is-beer').val() === '1';
                var volumeMl   = parseInt($(this).find('.liquor-volume-ml').val()) || 0;
                var lo         = readRowOffer($opt, liquorOfferMap);
                var qty        = parseInt($(this).find('.liquor-qty-input').val()) || 1;
                var disc       = rowTotalDiscount(lo.price, lo.ofType, lo.ofVal, qty, lo.buyQty, lo.getQty);
                var deductQty  = isBeer ? qty : qty * volumeMl;

                items.push({
                    food_item_id:  foodItemId,
                    quantity:      qty,
                    unit:          isBeer ? 'btl' : 'ml',
                    is_beer:       isBeer,
                    volume_ml:     isBeer ? null : volumeMl,
                    deduct_qty:    deductQty,
                    unit_price:    lo.price,
                    offer_applied: lo.ofType ? { type_slug: lo.ofType, discount_value: lo.ofVal, buy_qty: lo.buyQty, get_qty: lo.getQty } : null,
                    total_amount:  parseFloat((lo.price * qty - disc).toFixed(2)),
                });
            });

            if (!items.length) { toastr.warning('Please add at least one item.'); return; }
            if (!valid)        { toastr.warning('Please select an item for each row.'); return; }

            // Recompute totals for submission (GST on food only)
            var foodSubtotal = 0, foodDiscount = 0, liquorSubtotal = 0, liquorDiscount = 0;
            $('#foodTableBody .food-order-row').each(function () {
                var o = readRowOffer($(this).find('.food-item-sel option:selected'), foodOfferMap);
                var q = parseInt($(this).find('.food-qty-input').val()) || 0;
                foodSubtotal += o.price * q;
                foodDiscount += rowTotalDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
            });
            $('#liquorTableBody .liquor-order-row').each(function () {
                var o = readRowOffer($(this).find('.liquor-item-sel option:selected'), liquorOfferMap);
                var q = parseInt($(this).find('.liquor-qty-input').val()) || 0;
                liquorSubtotal += o.price * q;
                liquorDiscount += rowTotalDiscount(o.price, o.ofType, o.ofVal, q, o.buyQty, o.getQty);
            });
            var subtotal      = foodSubtotal + liquorSubtotal;
            var totalDiscount = foodDiscount + liquorDiscount;
            var gst           = Math.round((foodSubtotal - foodDiscount) * GST_RATE * 100) / 100;
            var grand         = parseFloat((subtotal - totalDiscount + gst).toFixed(2));

            var memberId = $('#cardentry').data('member-id');
            var $btn     = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

            $.ajax({
                url:         '{{ route("restaurant-orders.store") }}',
                type:        'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    _token:          '{{ csrf_token() }}',
                    member_id:       memberId,
                    items:           items,
                    taxable_amount:  parseFloat(subtotal.toFixed(2)),
                    discount_amount: parseFloat(totalDiscount.toFixed(2)),
                    gst_amount:      gst,
                    net_amount:      grand,
                }),
                success: function (response) {
                    if (response.statusCode == 200) {
                        toastr.success('Order placed! Order No: ' + response.order_no);
                        $('#createOrderModal').modal('hide');
                        $('#cardMemberWallet').text('Rs.' + response.wallet_balance);
                    } else if (response.statusCode == 422 && response.wallet_balance) {
                        toastr.error(
                            'Insufficient wallet balance.<br>Available: Rs.' + response.wallet_balance +
                            ' &nbsp;|&nbsp; Required: Rs.' + response.required_amount,
                            '', { escapeHtml: false }
                        );
                    } else {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                    $btn.prop('disabled', false).html($btn.attr('id') === 'payWithWalletBtn' ? 'Wallet' : 'Place Order');
                },
                error: function () {
                    toastr.error('Something went wrong.');
                    $btn.prop('disabled', false).html($btn.attr('id') === 'payWithWalletBtn' ? 'Wallet' : 'Place Order');
                }
            });
        });

    });
    </script>
</body>

</html>
