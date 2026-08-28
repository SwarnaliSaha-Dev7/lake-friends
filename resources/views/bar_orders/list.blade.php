@extends('base.app')
@section('title', $title)
@section('page_title', $page_title)

@section('content')

    {{-- Header row --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div></div>
        <div class="d-flex gap-2">
            <a href="{{ route('bar-orders.history') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> History
            </a>
            <button type="button" class="btn btn-primary fw-semibold" id="newBarOrderBtn">
                <i class="fa-solid fa-plus me-1"></i> New Bar Order
            </button>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg,#29b6f6,#0288d1);">
                <div>
                    <div class="small opacity-75 mb-1">Top Selling Liquor</div>
                    <div class="fs-5 fw-bold">{{ $topSellingLiquor }}</div>
                </div>
                <i class="fa-solid fa-wine-bottle fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg,#66bb6a,#388e3c);">
                <div>
                    <div class="small opacity-75 mb-1">Total Selling</div>
                    <div class="fs-5 fw-bold">₹{{ number_format($totalSelling, 0) }}</div>
                </div>
                <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg,#ffa726,#e65100);">
                <div>
                    <div class="small opacity-75 mb-1">Today's Sale</div>
                    <div class="fs-5 fw-bold">₹{{ number_format($todaySale, 0) }}</div>
                </div>
                <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Today's bar orders table --}}
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <h2 class="fs-5 common-heading mb-3 fw-semibold">Today's Bar Orders</h2>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Order No</th>
                                <th class="text-white fw-medium text-nowrap">Member</th>
                                <th class="text-white fw-medium text-nowrap">Time</th>
                                <th class="text-white fw-medium text-nowrap">Items</th>
                                <th class="text-white fw-medium text-nowrap">Amount</th>
                                <th class="text-white fw-medium text-nowrap">Status</th>
                                <th class="text-white fw-medium text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @forelse($orders as $order)
                                @php
                                    $liquorItems = $order->items->whereIn('unit', ['ml', 'btl']);
                                    $grandTotal += $order->net_amount;
                                    $isDirectBarOrder = is_null($order->session_id);
                                    $statusColors = [
                                        'paid'      => 'bg-warning-subtle text-warning border-warning',
                                        'pending'   => 'bg-warning-subtle text-warning border-warning',
                                        'delivered' => 'bg-primary-subtle text-primary border-primary',
                                        'cancelled' => 'bg-danger-subtle text-danger border-danger',
                                    ];
                                    $statusLabel = $order->status === 'delivered' ? 'Served' : ucfirst($order->status);
                                @endphp
                                <tr id="bar-order-row-{{ $order->id }}">
                                    <td class="text-nowrap fw-medium">{{ $order->order_no }}</td>
                                    <td class="text-nowrap">{{ $order->session?->order_person_name ?: ($order->member->name ?? '—') }}</td>
                                    <td class="text-nowrap text-muted small">{{ $order->created_at->format('h:i A') }}</td>
                                    <td>
                                        @foreach($liquorItems as $item)
                                            <div class="small text-nowrap">
                                                {{ !empty($item->metadata['is_cocktail']) ? ($item->metadata['cocktail_name'] ?? ($item->foodItem->name ?? '—')) : ($item->foodItem->name ?? '—') }}
                                                <span class="text-muted">
                                                    @if($item->unit === 'btl')
                                                        &times;{{ $item->quantity }} BTL
                                                    @else
                                                        {{ $item->metadata['volume_ml'] ?? '?' }}ml &times;{{ $item->quantity }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($order->net_amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $statusColors[$order->status] ?? 'bg-secondary-subtle text-secondary border-secondary' }} bar-order-status-{{ $order->id }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 view-bar-order-btn" data-id="{{ $order->id }}" title="View">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @if($isDirectBarOrder && in_array($order->status, ['paid', 'pending']))
                                            <button type="button" class="btn btn-sm ms-1 px-2 py-1 fw-semibold text-white mark-served-btn" style="background:#4f46e5;" data-id="{{ $order->id }}">
                                                <i class="fa-regular fa-circle-check me-1"></i>Served
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1 px-2 py-1 fw-semibold cancel-bar-order-btn" data-id="{{ $order->id }}" data-amount="Rs {{ number_format($order->net_amount, 2) }}">
                                                Cancel
                                            </button>
                                        @elseif($isDirectBarOrder && $order->status === 'delivered')
                                            <button class="btn btn-sm ms-1 px-2 py-1 fw-semibold text-white" style="background:#4f46e5;pointer-events:none;" disabled>
                                                <i class="fa-solid fa-circle-check me-1"></i>Served
                                            </button>
                                        @elseif($isDirectBarOrder && $order->status === 'cancelled')
                                            <button class="btn btn-sm ms-1 px-2 py-1 fw-semibold text-white" style="background:#6c757d;pointer-events:none;" disabled>Cancelled</button>
                                        @else
                                            <span class="text-muted small ms-1">Via Session {{ $order->session->session_no ?? '' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No bar orders today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($orders->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold" style="background:#f1f3f5;">
                                <td colspan="4" class="text-end pe-3">Total</td>
                                <td class="text-nowrap text-primary">Rs {{ number_format($grandTotal, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modalComponent')

    {{-- ═══════════════════════════════ NEW ORDER MODAL ══════════════════════════════ --}}
    <div class="modal fade" id="newBarOrderModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fa-solid fa-martini-glass-citrus me-2 text-primary"></i>New Bar Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeNewOrderModal"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="row g-3">

                        {{-- LEFT: Member + Cart --}}
                        <div class="col-lg-4">

                            {{-- Member search --}}
                            <div class="p-3 rounded-3 border mb-3">
                                <div class="fw-semibold mb-2 small text-muted text-uppercase">Member</div>
                                <div class="input-group mb-2">
                                    <input type="text" id="barCardInput" class="form-control shadow-none"
                                        placeholder="Scan / enter card no.">
                                    <button class="btn btn-outline-primary" id="barFetchMemberBtn" type="button">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                                <div id="barMemberInfo" style="display:none;">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light">
                                        <div>
                                            <div class="fw-semibold" id="barMemberName"></div>
                                            <div class="small text-muted" id="barMemberEmail"></div>
                                            <div class="small mt-1">
                                                Wallet: <span class="fw-bold text-success" id="barWalletBalance"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="barMemberError" class="text-danger small mt-1" style="display:none;"></div>
                                <input type="hidden" id="barMemberId">
                            </div>

                            {{-- Cart --}}
                            <div class="p-3 rounded-3 border">
                                <div class="fw-semibold mb-2 small text-muted text-uppercase">Order Summary</div>
                                <div id="barCartEmpty" class="text-muted small text-center py-3">
                                    <i class="fa-solid fa-cart-shopping mb-1 d-block fs-4 opacity-25"></i>
                                    No items added yet
                                </div>
                                <div id="barCartItems"></div>

                                <div id="barCartTotals" style="display:none;">
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="fw-semibold" id="cartSubtotal">Rs 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1" id="cartDiscountRow" style="display:none!important;">
                                        <span class="text-success"><i class="fa-solid fa-tag me-1"></i>Offer Savings</span>
                                        <span class="fw-semibold text-success" id="cartDiscount">- Rs 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Beverage GST</span>
                                        <span class="fw-semibold" id="cartGstAmt">Rs 0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-1">
                                        <span>Total</span>
                                        <span class="text-primary fs-5" id="cartTotal">Rs 0.00</span>
                                    </div>
                                </div>

                                <button type="button" id="placeBarOrderBtn"
                                    class="btn btn-primary w-100 fw-semibold mt-3" style="display:none;">
                                    <i class="fa-solid fa-check me-1"></i> Place Order
                                </button>
                            </div>
                        </div>

                        {{-- RIGHT: Item selector --}}
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <div class="fw-semibold small text-muted text-uppercase me-auto">Bar Items</div>
                                <input type="text" id="barItemSearch" class="form-control form-control-sm shadow-none"
                                    style="max-width:200px;" placeholder="Search item...">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary active bar-type-filter" data-filter="all">All</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="spirit">Spirit</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="beer">Beer</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="wine">Wine</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="cocktail">Cocktail</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="mocktail">Mocktail</button>
                                    <button type="button" class="btn btn-outline-secondary bar-type-filter" data-filter="beverage">Beverage</button>
                                </div>
                            </div>
                            <div id="barItemsGrid" class="row g-2" style="max-height:420px;overflow-y:auto;">
                                <div class="col-12 text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm"></div> Loading...
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel confirm modal --}}
    <div class="modal fade" id="cancelBarOrderModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width:56px;height:56px;background:#fee2e2;">
                            <i class="fa-solid fa-rotate-left fs-4 text-danger"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold mb-1">Cancel Order?</h5>
                    <p class="text-muted small mb-1">This will cancel the order, restore bar stock, and refund</p>
                    <p class="fw-bold text-danger mb-3" id="cancelBarRefundAmount"></p>
                    <p class="text-muted small mb-4">to the member's wallet.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-light px-4" data-bs-dismiss="modal">Keep</button>
                        <button class="btn btn-danger px-4" id="confirmCancelBarBtn">Yes, Cancel & Refund</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- View order modal --}}
    <div class="modal fade" id="viewBarOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-semibold">Bar Order Details</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewBarOrderBody">
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

    // GST is per-item now (0% liquor, {{ $globalBeverageGstPercentage }}% beverage) — see gst_rate on each bar item.
    var MASTER_BEVERAGE_GST_RATE = {{ $globalBeverageGstPercentage }};
    var MASTER_FOOD_GST_RATE     = {{ $globalRestaurantGstPercentage }};
    var barItems  = [];
    var cart      = [];   // [{id, name, is_beer, volume_ml, quantity, deduct_qty, unit_price, total}]

    // ── Load bar items ──────────────────────────────────────────────────────
    function loadBarItems() {
        $.get('{{ route("bar-orders.items") }}', function (res) {
            if (res.statusCode === 200) {
                barItems = res.items;
                renderItems(barItems);
            }
        });
    }

    function renderItems(items) {
        var html = '';
        if (!items.length) {
            html = '<div class="col-12 text-center text-muted py-4">No items found.</div>';
        }

        // Sort: in-stock first, low-stock next, out-of-stock / b1g1-insufficient last
        var sorted = items.slice().sort(function (a, b) {
            var rank = function (x) {
                if (!x.in_stock) return 3;
                if (x.is_beer && x.offer && x.offer.type_slug === 'b1g1' &&
                    x.bar_stock < (x.offer.buy_qty || 1) + (x.offer.get_qty || 1)) return 2;
                if (x.is_low) return 1;
                return 0;
            };
            return rank(a) - rank(b);
        });

        sorted.forEach(function (item) {
            var isOut = !item.in_stock;
            var isLow = item.is_low;

            // B1G1 specific: need buy_qty + get_qty bottles in stock
            var isB1g1Insufficient = false;
            if (!isOut && item.is_beer && item.offer && item.offer.type_slug === 'b1g1') {
                var b1g1Need = (item.offer.buy_qty || 1) + (item.offer.get_qty || 1);
                isB1g1Insufficient = item.bar_stock < b1g1Need;
            }

            var stockDisplay, typeBadge, sizeTxt, itemType;
            if (item.is_cocktail && item.is_mocktail) {
                // Non-alcoholic "cocktail" built on a beverage base (Masala Coke,
                // Virgin Mojito etc.) — deducted by the bottle, not ml.
                stockDisplay = item.btl_eq + ' servings';
                typeBadge    = '<span class="badge text-white" style="font-size:0.65rem;background:#0e7490;">Mocktail</span>';
                sizeTxt      = item.size_ml + ' BTL';
                itemType     = 'mocktail';
            } else if (item.is_cocktail) {
                stockDisplay = item.btl_eq + ' servings';
                typeBadge    = '<span class="badge text-white" style="font-size:0.65rem;background:#7c3aed;">Cocktail</span>';
                sizeTxt      = item.size_ml + ' ml';
                itemType     = 'cocktail';
            } else if (item.is_beverage) {
                stockDisplay = item.bar_stock + ' BTL';
                typeBadge    = '<span class="badge text-white" style="font-size:0.65rem;background:#0dcaf0;">Beverage</span>';
                sizeTxt      = item.size_ml ? item.size_ml + ' ml' : '';
                itemType     = 'beverage';
            } else if (item.is_beer && String(item.category).toLowerCase() === 'wine') {
                // Wine — sold whole-bottle like beer (750/375/187ml etc.), just labeled distinctly
                stockDisplay = item.bar_stock + ' BTL';
                typeBadge    = '<span class="badge text-white" style="font-size:0.65rem;background:#9d174d;">Wine</span>';
                sizeTxt      = item.size_ml ? item.size_ml + ' ml' : '';
                itemType     = 'wine';
            } else if (item.is_beer) {
                stockDisplay = item.bar_stock + ' BTL';
                typeBadge    = '<span class="badge bg-warning text-dark" style="font-size:0.65rem;">Beer</span>';
                sizeTxt      = '';
                itemType     = 'beer';
            } else {
                // Spirit peg — fixed serving volume/price from Liquor Servings
                stockDisplay = item.btl_eq + ' servings (' + item.bar_stock.toLocaleString() + ' ml left)';
                typeBadge    = '<span class="badge bg-info text-white" style="font-size:0.65rem;">Spirit</span>';
                sizeTxt      = item.size_ml ? item.size_ml + ' ml' : '';
                itemType     = 'spirit';
            }

            var stockColor  = (isOut || isB1g1Insufficient) ? '#dc3545' : (isLow ? '#fd7e14' : '#198754');
            var stockLabel  = isOut ? 'Out of Stock'
                            : isB1g1Insufficient ? 'Insufficient for B1G1'
                            : (isLow ? 'Low Stock' : 'In Stock');
            var cardBorder  = (isOut || isB1g1Insufficient) ? 'border-danger' : (isLow ? 'border-warning' : item.is_mocktail ? 'border-info' : item.is_cocktail ? 'border-purple' : '');
            var cardBg      = (isOut || isB1g1Insufficient) ? 'background:#fff5f5;'
                            : isLow ? 'background:#fffbf0;'
                            : item.is_mocktail ? 'background:#ecfeff;'
                            : item.is_cocktail ? 'background:#faf5ff;' : '';

            var offerBadge = '';
            if (item.offer) {
                var o = item.offer;
                var offerLabel = o.offer_name;
                if (o.type_slug === 'percentage') {
                    offerLabel = o.discount_value + '% OFF';
                } else if (o.type_slug === 'flat') {
                    offerLabel = 'Rs ' + o.discount_value + ' OFF';
                } else if (o.type_slug === 'b1g1') {
                    offerLabel = 'Buy ' + o.buy_qty + ' Get ' + o.get_qty;
                }
                offerBadge = '<span class="badge bg-danger text-white rounded-pill px-2 mt-1" style="font-size:0.68rem;"><i class="fa-solid fa-tag me-1"></i>' + offerLabel + '</span>';
            }

            var priceLabel = item.is_beer ? '/BTL' : '/unit';
            var servingId  = item.serving_id || '';
            var mixerNote  = item.secondary_item_name
                ? '<div class="text-muted" style="font-size:0.72rem;"><i class="fa-solid fa-plus me-1"></i>Includes ' + item.secondary_quantity + ' BTL ' + item.secondary_item_name + '</div>'
                : '';

            html += '<div class="col-sm-6 col-md-4 bar-item-card" data-name="' + item.name.toLowerCase() + '" data-type="' + itemType + '">'
                + '<div class="border rounded-3 p-2 h-100 d-flex flex-column ' + cardBorder + '" style="font-size:0.82rem;' + cardBg + '">'
                +   '<div class="d-flex align-items-start justify-content-between mb-1">'
                +     '<span class="fw-semibold">' + item.name + '</span>'
                +     typeBadge
                +   '</div>'
                +   '<div class="text-muted mb-1">' + item.category + (sizeTxt ? ' · ' + sizeTxt + (item.is_cocktail ? ' deduct' : '') : '') + '</div>'
                +   mixerNote
                +   '<div class="mb-1 d-flex align-items-center gap-2">'
                +     '<span class="fw-bold" style="color:' + stockColor + ';font-size:0.78rem;">'
                +       '<i class="fa-solid fa-' + ((isOut || isB1g1Insufficient) ? 'circle-xmark' : (isLow ? 'triangle-exclamation' : 'circle-check')) + ' me-1"></i>'
                +       stockLabel
                +     '</span>'
                +     '<span class="text-muted" style="font-size:0.75rem;">' + stockDisplay + '</span>'
                +   '</div>'
                +   (offerBadge ? '<div class="mb-1">' + offerBadge + '</div>' : '')
                +   '<div class="fw-bold text-primary mb-2">Rs ' + item.price.toFixed(2) + priceLabel + '</div>'
                +   '<button type="button" class="btn btn-sm btn-outline-primary mt-auto add-bar-item-btn"'
                +     ' data-id="' + item.id + '"'
                +     ' data-serving-id="' + servingId + '"'
                +     ' data-name="' + item.name + '"'
                +     ' data-is-beer="' + (item.is_beer ? '1' : '0') + '"'
                +     ' data-is-cocktail="' + (item.is_cocktail ? '1' : '0') + '"'
                +     ' data-size-ml="' + item.size_ml + '"'
                +     ' data-price="' + item.price + '"'
                +     ' data-gst-rate="' + (item.gst_rate || 0) + '"'
                +     ' data-stock="' + item.bar_stock + '"'
                +     ' data-secondary-name="' + (item.secondary_item_name || '') + '"'
                +     ' data-secondary-qty="' + (item.secondary_quantity || 0) + '"'
                +     ' data-secondary-stock="' + (item.secondary_stock || 0) + '"'
                +     ((item.in_stock && !isB1g1Insufficient) ? '' : ' disabled')
                +   '><i class="fa-solid fa-plus me-1"></i>Add</button>'
                + '</div></div>';
        });
        $('#barItemsGrid').html(html);
    }

    // ── Filter / search ─────────────────────────────────────────────────────
    $(document).on('click', '.bar-type-filter', function () {
        $('.bar-type-filter').removeClass('active');
        $(this).addClass('active');
        applyFilter();
    });

    $('#barItemSearch').on('input', applyFilter);

    function applyFilter() {
        var search = $('#barItemSearch').val().toLowerCase();
        var type   = $('.bar-type-filter.active').data('filter');
        $('.bar-item-card').each(function () {
            var nameMatch = $(this).data('name').indexOf(search) !== -1;
            var typeMatch = type === 'all' || $(this).data('type') === type;
            $(this).toggle(nameMatch && typeMatch);
        });
    }

    // ── Member fetch ────────────────────────────────────────────────────────
    $('#barFetchMemberBtn').on('click', fetchMember);
    $('#barCardInput').on('keydown', function (e) { if (e.key === 'Enter') fetchMember(); });

    function fetchMember() {
        var card = $('#barCardInput').val().trim();
        if (!card) return;
        $('#barMemberInfo').hide();
        $('#barMemberError').hide();

        $.get('{{ route("getMemberDetails", ":card") }}'.replace(':card', card), function (res) {
            if (res.statusCode === 200) {
                var m = res.data;
                $('#barMemberId').val(m.id);
                $('#barMemberName').text(m.name);
                $('#barMemberEmail').text(m.email || '');
                var bal = m.wallet_details ? parseFloat(m.wallet_details.current_balance || 0) : 0;
                $('#barWalletBalance').text('Rs ' + bal.toFixed(2));
                $('#barMemberInfo').show();
            } else {
                $('#barMemberError').text(res.error || 'Member not found.').show();
            }
        }).fail(function () {
            $('#barMemberError').text('Server error.').show();
        });
    }

    // ── Add item ────────────────────────────────────────────────────────────
    $(document).on('click', '.add-bar-item-btn', function () {
        var $btn       = $(this);
        var isBeer     = $btn.data('is-beer') == '1';
        var isCocktail = $btn.data('is-cocktail') == '1';
        var id         = $btn.data('id');
        var servingId  = $btn.data('serving-id') || null;
        var name       = $btn.data('name');
        var price      = parseFloat($btn.data('price'));
        var stock      = parseInt($btn.data('stock'));
        var sizeMl     = parseInt($btn.data('size-ml')) || 0;
        var gstRate    = parseFloat($btn.data('gst-rate')) || 0;
        var secondaryName = $btn.data('secondary-name') || '';
        var secondaryQty  = parseInt($btn.data('secondary-qty')) || 0;

        var barItem = barItems.find(function (bi) {
            return servingId
                ? String(bi.serving_id) === String(servingId)
                : String(bi.id) === String(id) && !bi.is_cocktail;
        });
        var offer = barItem ? (barItem.offer || null) : null;

        if (servingId) {
            // Spirit peg or cocktail — fixed volume & price from Liquor Servings,
            // added straight to cart (no free-form peg size, no raw-item price).
            var deductQty = sizeMl;
            if (deductQty > stock) {
                toastr.error('Not enough bar stock for "' + name + '".');
                return;
            }
            addToCart({
                id: id, serving_id: servingId, name: name,
                is_beer: isBeer, is_cocktail: isCocktail,
                volume_ml: sizeMl,
                paid_qty: 1, free_qty: 0, quantity: 1,
                deduct_qty: deductQty,
                unit_price: price, bar_stock: stock, offer: offer, gst_rate: gstRate,
                secondary_name: secondaryName, secondary_qty: secondaryQty,
            });
        } else if (isBeer) {
            if (offer && offer.type_slug === 'b1g1') {
                var buyQty = offer.buy_qty || 1;
                var getQty = offer.get_qty || 1;
                var totalNeeded = buyQty + getQty;
                if (stock < totalNeeded) {
                    toastr.error(
                        '"' + name + '" এ Buy ' + buyQty + ' Get ' + getQty +
                        ' offer আছে। কমপক্ষে ' + totalNeeded + ' BTL stock থাকতে হবে।' +
                        ' বর্তমানে মাত্র ' + stock + ' BTL available।'
                    );
                    return;
                }
                addToCart({
                    id: id, serving_id: null, name: name, is_beer: true, is_cocktail: false,
                    volume_ml: null, paid_qty: buyQty, free_qty: getQty,
                    quantity: buyQty + getQty,
                    deduct_qty: buyQty + getQty,
                    unit_price: price, bar_stock: stock, offer: offer, gst_rate: gstRate,
                });
            } else {
                addToCart({ id: id, serving_id: null, name: name, is_beer: true, is_cocktail: false, volume_ml: null, paid_qty: 1, free_qty: 0, quantity: 1, deduct_qty: 1, unit_price: price, bar_stock: stock, offer: offer, gst_rate: gstRate });
            }
        } else {
            // Spirit item with no Liquor Serving configured yet — nothing to sell it as.
            toastr.error('"' + name + '" has no peg/serving price set up yet. Add one from Liquor Servings first.');
        }
    });

    function addToCart(item) {
        if (item.paid_qty === undefined) item.paid_qty = item.quantity;
        if (item.free_qty === undefined) item.free_qty = 0;

        // Merge: cocktails merge by serving_id; regular items merge by food_item_id + volume_ml
        var existing = cart.find(function (c) {
            if (item.is_cocktail && c.is_cocktail) {
                return String(c.serving_id) === String(item.serving_id);
            }
            return !c.is_cocktail && !item.is_cocktail && c.id === item.id && c.volume_ml === item.volume_ml;
        });
        if (existing) {
            var newDeduct = existing.deduct_qty + item.deduct_qty;
            if (newDeduct > item.bar_stock) {
                toastr.error('Not enough bar stock.');
                return;
            }
            existing.paid_qty   = (existing.paid_qty || existing.quantity) + item.paid_qty;
            existing.free_qty   = (existing.free_qty || 0) + item.free_qty;
            existing.quantity   = existing.paid_qty + existing.free_qty;
            existing.deduct_qty = newDeduct;
        } else {
            cart.push(item);
        }
        renderCart();
    }

    function renderCart() {
        if (!cart.length) {
            $('#barCartEmpty').show();
            $('#barCartItems').html('');
            $('#barCartTotals').hide();
            $('#placeBarOrderBtn').hide();
            return;
        }

        $('#barCartEmpty').hide();

        var html = '';
        cart.forEach(function (item, idx) {
            var paidQty = item.paid_qty !== undefined ? item.paid_qty : item.quantity;
            var freeQty = item.free_qty || 0;
            var desc, lineTotal;

            if (item.is_beer) {
                if (freeQty > 0) {
                    desc = paidQty + ' BTL + <span class="text-success fw-semibold">' + freeQty + ' Free</span> = ' + item.quantity + ' BTL';
                } else {
                    desc = item.quantity + ' BTL';
                }
                lineTotal = paidQty * item.unit_price;
            } else {
                desc = item.quantity + ' × ' + item.volume_ml + ' ml = ' + item.deduct_qty.toLocaleString() + ' ml';
                lineTotal = paidQty * item.unit_price;
            }

            var offerTag = (item.offer && item.offer.type_slug === 'b1g1')
                ? ' <span class="badge bg-danger text-white rounded-pill px-1" style="font-size:0.6rem;">B1G1</span>'
                : '';
            var gstTag = (item.gst_rate > 0)
                ? ' <span class="text-muted" style="font-size:0.68rem;">+' + item.gst_rate + '% GST</span>'
                : '';
            var mixerTag = item.secondary_name
                ? '<div class="text-muted fst-italic" style="font-size:0.7rem;">+ ' + (item.secondary_qty * item.quantity) + ' BTL ' + item.secondary_name + ' (cost included above, no separate bill line)</div>'
                : '';

            html += '<div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">'
                +   '<div class="flex-grow-1">'
                +     '<div class="fw-semibold small">' + item.name + offerTag + '</div>'
                +     '<div class="text-muted" style="font-size:0.75rem;">' + desc + ' · Rs ' + item.unit_price.toFixed(2) + '/unit' + gstTag + '</div>'
                +     mixerTag
                +   '</div>'
                +   '<div class="text-end">'
                +     '<div class="fw-bold small">Rs ' + lineTotal.toFixed(2) + '</div>'
                +     '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-cart-item" data-idx="' + idx + '" style="font-size:0.7rem;">Remove</button>'
                +   '</div>'
                + '</div>';
        });

        $('#barCartItems').html(html);

        var subtotal    = cart.reduce(function (s, c) {
            var paidQty = c.paid_qty !== undefined ? c.paid_qty : c.quantity;
            return s + paidQty * c.unit_price;
        }, 0);
        var discountAmt = cart.reduce(function (s, c) {
            return s + (c.free_qty || 0) * c.unit_price;
        }, 0);
        var gstAmt = cart.reduce(function (s, c) {
            var paidQty = c.paid_qty !== undefined ? c.paid_qty : c.quantity;
            return s + (paidQty * c.unit_price) * ((c.gst_rate || 0) / 100);
        }, 0);
        var total  = subtotal + gstAmt;

        $('#cartSubtotal').text('Rs ' + subtotal.toFixed(2));
        if (discountAmt > 0) {
            $('#cartDiscount').text('- Rs ' + discountAmt.toFixed(2));
            $('#cartDiscountRow').show();
        } else {
            $('#cartDiscountRow').hide();
        }
        $('#cartGstAmt').text('Rs ' + gstAmt.toFixed(2));
        $('#cartTotal').text('Rs ' + total.toFixed(2));
        $('#barCartTotals').show();
        $('#placeBarOrderBtn').show();
    }

    // Remove cart item
    $(document).on('click', '.remove-cart-item', function () {
        cart.splice(parseInt($(this).data('idx')), 1);
        renderCart();
    });

    // ── Open modal ──────────────────────────────────────────────────────────
    $('#newBarOrderBtn').on('click', function () {
        cart = [];
        renderCart();
        $('#barCardInput').val('');
        $('#barMemberId').val('');
        $('#barMemberInfo').hide();
        $('#barMemberError').hide();
        $('#newBarOrderModal').modal('show');
        if (!barItems.length) loadBarItems();
    });

    // ── Place order ─────────────────────────────────────────────────────────
    $('#placeBarOrderBtn').on('click', function () {
        var memberId = $('#barMemberId').val();
        if (!memberId) { toastr.warning('Please select a member first.'); return; }
        if (!cart.length) { toastr.warning('Cart is empty.'); return; }

        var subtotal    = cart.reduce(function (s, c) {
            var paidQty = c.paid_qty !== undefined ? c.paid_qty : c.quantity;
            return s + paidQty * c.unit_price;
        }, 0);
        var discountAmt = cart.reduce(function (s, c) {
            return s + (c.free_qty || 0) * c.unit_price;
        }, 0);
        var gstAmt = cart.reduce(function (s, c) {
            var paidQty = c.paid_qty !== undefined ? c.paid_qty : c.quantity;
            return s + (paidQty * c.unit_price) * ((c.gst_rate || 0) / 100);
        }, 0);
        var netAmt = subtotal + gstAmt;
        // Blended rate for the order's single gst_percentage record — items can mix
        // 0% liquor and 5% beverage lines, so this is an effective/average, not a fixed rate.
        var blendedGstPct = subtotal > 0 ? Math.round((gstAmt / subtotal) * 10000) / 100 : 0;

        var items = cart.map(function (c) {
            var paidQty = c.paid_qty !== undefined ? c.paid_qty : c.quantity;
            return {
                food_item_id:  c.id,
                serving_id:    c.serving_id || null,
                name:          c.name,
                is_beer:       c.is_beer,
                is_cocktail:   c.is_cocktail || false,
                volume_ml:     c.volume_ml,
                quantity:      c.quantity,
                paid_qty:      paidQty,
                free_qty:      c.free_qty || 0,
                deduct_qty:    c.deduct_qty,
                unit_price:    c.unit_price,
                total_amount:  paidQty * c.unit_price,
                offer_applied: c.offer || null,
            };
        });

        var $btn = $('#placeBarOrderBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Placing...');

        $.ajax({
            url: '{{ route("bar-orders.store") }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:           '{{ csrf_token() }}',
                member_id:        memberId,
                items:            items,
                taxable_amount:   subtotal.toFixed(2),
                discount_amount:  discountAmt.toFixed(2),
                gst_percentage:   blendedGstPct,
                gst_amount:       gstAmt.toFixed(2),
                net_amount:       netAmt.toFixed(2),
            }),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#newBarOrderModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(res.message || 'Order failed.');
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Server error.';
                toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Place Order');
            }
        });
    });

    // ── Mark served ─────────────────────────────────────────────────────────
    $(document).on('click', '.mark-served-btn', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url:  '{{ route("bar-orders.serve", ":id") }}'.replace(':id', id),
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $btn.replaceWith(
                        '<button class="btn btn-sm ms-1 px-2 py-1 fw-semibold text-white"'
                        + ' style="background:#4f46e5;pointer-events:none;" disabled>'
                        + '<i class="fa-solid fa-circle-check me-1"></i>Served</button>'
                    );
                    $('.bar-order-status-' + id)
                        .removeClass().addClass('badge border rounded-pill px-3 py-1 bg-primary-subtle text-primary border-primary bar-order-status-' + id)
                        .text('Served');
                    $('#bar-order-row-' + id + ' .cancel-bar-order-btn').remove();
                } else {
                    toastr.error(res.message || 'Error.');
                    $btn.prop('disabled', false).html('<i class="fa-regular fa-circle-check me-1"></i>Served');
                }
            },
            error: function () {
                toastr.error('Server error.');
                $btn.prop('disabled', false).html('<i class="fa-regular fa-circle-check me-1"></i>Served');
            }
        });
    });

    // ── Cancel order ────────────────────────────────────────────────────────
    $(document).on('click', '.cancel-bar-order-btn', function () {
        $('#cancelBarRefundAmount').text($(this).data('amount'));
        $('#confirmCancelBarBtn').data('id', $(this).data('id'));
        $('#cancelBarOrderModal').modal('show');
    });

    $('#confirmCancelBarBtn').on('click', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url:  '{{ route("bar-orders.cancel", ":id") }}'.replace(':id', id),
            type: 'PATCH',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#cancelBarOrderModal').modal('hide');
                    $('.bar-order-status-' + id)
                        .removeClass().addClass('badge border rounded-pill px-3 py-1 bg-danger-subtle text-danger border-danger bar-order-status-' + id)
                        .text('Cancelled');
                    var $row = $('#bar-order-row-' + id);
                    $row.find('.mark-served-btn, .cancel-bar-order-btn').remove();
                    $row.find('td:last-child').append(
                        '<button class="btn btn-sm ms-1 px-2 py-1 fw-semibold text-white"'
                        + ' style="background:#6c757d;pointer-events:none;" disabled>Cancelled</button>'
                    );
                } else {
                    toastr.error(res.message || 'Error.');
                }
                $btn.prop('disabled', false).html('Yes, Cancel & Refund');
            },
            error: function () { toastr.error('Server error.'); $btn.prop('disabled', false).html('Yes, Cancel & Refund'); }
        });
    });

    // ── View order ──────────────────────────────────────────────────────────
    $(document).on('click', '.view-bar-order-btn', function () {
        var id = $(this).data('id');
        $('#viewBarOrderBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $('#viewBarOrderModal').modal('show');

        $.get('{{ route("bar-orders.show", ":id") }}'.replace(':id', id), function (res) {
            if (res.statusCode !== 200) {
                $('#viewBarOrderBody').html('<p class="text-danger text-center py-4">Failed to load.</p>');
                return;
            }
            var o = res.data;
            var rows = '';
            var liquorTotal = 0;
            var foodTotal = 0;
            // A Bar Order can come from a session that also billed food in the
            // same order (Food + Liquor + Beverage together) — those food lines
            // still count toward this order's total and its GST, so they must be
            // shown here too, or the Subtotal/GST/Total Charged won't reconcile.
            (o.items || []).filter(function (it) {
                return it.unit === 'plate';
            }).forEach(function (it) {
                var amt = parseFloat(it.total_amount);
                foodTotal += amt;
                rows += '<tr>'
                    + '<td class="fw-medium">' + (it.food_item ? it.food_item.name : '—') + '</td>'
                    + '<td class="text-center text-nowrap">Qty ' + it.quantity + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + parseFloat(it.unit_price).toFixed(2) + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + amt.toFixed(2) + '</td>'
                    + '</tr>';
            });
            (o.items || []).filter(function (it) {
                return it.unit === 'ml' || it.unit === 'btl';
            }).forEach(function (it) {
                var isBeer  = it.unit === 'btl';
                var meta    = it.metadata || {};
                var offer   = it.offer_applied || null;
                var volDesc, offerTag = '';

                if (isBeer) {
                    if (offer && offer.type_slug === 'b1g1') {
                        var buyQty  = offer.buy_qty || 1;
                        var getQty  = offer.get_qty || 1;
                        var setSize = buyQty + getQty;
                        var sets    = setSize > 0 ? Math.floor(it.quantity / setSize) : 0;
                        var paid    = sets * buyQty;
                        var free    = sets * getQty;
                        volDesc = paid + ' BTL + <span class="text-success fw-semibold">' + free + ' Free</span> = ' + it.quantity + ' BTL';
                    } else {
                        volDesc = it.quantity + ' BTL';
                    }
                } else {
                    volDesc = (meta.volume_ml || '?') + ' ml × ' + it.quantity + ' = ' + (it.quantity * (meta.volume_ml || 0)) + ' ml total';
                }

                if (offer) {
                    var oLabel = offer.offer_name || '';
                    if (offer.type_slug === 'b1g1')       oLabel = 'Buy ' + (offer.buy_qty||1) + ' Get ' + (offer.get_qty||1);
                    else if (offer.type_slug === 'percentage') oLabel = (offer.discount_value||'') + '% OFF';
                    else if (offer.type_slug === 'flat')   oLabel = 'Rs ' + (offer.discount_value||'') + ' OFF';
                    offerTag = ' <span class="badge bg-danger rounded-pill px-1" style="font-size:0.6rem;"><i class="fa-solid fa-tag me-1"></i>' + oLabel + '</span>';
                }

                var amt = parseFloat(it.total_amount);
                liquorTotal += amt;

                var displayName;
                if (meta.is_cocktail) {
                    var isMocktailItem = it.food_item && it.food_item.item_type === 'beverage';
                    var typeBadgeHtml = isMocktailItem
                        ? ' <span class="badge ms-1" style="font-size:0.6rem;background:#0e7490;color:#fff;">Mocktail</span>'
                        : ' <span class="badge ms-1" style="font-size:0.6rem;background:#7c3aed;color:#fff;">Cocktail</span>';
                    displayName = (meta.cocktail_name || (it.food_item ? it.food_item.name : '—'))
                        + typeBadgeHtml
                        + '<br><small class="text-muted">base: ' + (it.food_item ? it.food_item.name : '—') + '</small>';
                } else {
                    displayName = it.food_item ? it.food_item.name : '—';
                }

                rows += '<tr>'
                    + '<td class="fw-medium">' + displayName + offerTag + '</td>'
                    + '<td class="text-center text-nowrap">' + volDesc + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + parseFloat(it.unit_price).toFixed(2) + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + amt.toFixed(2) + '</td>'
                    + '</tr>';
            });

            var statusColors = { paid:'text-success', delivered:'text-primary', cancelled:'text-danger' };
            var statusLabel  = o.status === 'delivered' ? 'Served' : (o.status.charAt(0).toUpperCase() + o.status.slice(1));
            var sClass       = statusColors[o.status] || 'text-muted';

            if (liquorTotal === 0 && foodTotal === 0) {
                $('#viewBarOrderBody').html('<p class="text-muted text-center py-4">No liquor items in this order.</p>');
                return;
            }

            var discountAmt = parseFloat(o.discount_amount || 0);
            var gstAmt      = parseFloat(o.gst_amount || 0);
            var netAmt      = parseFloat(o.net_amount || 0);

            // Food GST is exact — the food subtotal is never touched by a hidden
            // cocktail mixer, so foodTotal * rate is always precisely right, no
            // guessing needed. Everything else in the stored gst_amount (real
            // beverage lines AND any hidden mixer inside a "liquor" cocktail line,
            // which is always a beverage) is taxed at the same beverage rate, so
            // it's shown as one "Beverage GST" figure — the exact remainder after
            // food's share is removed. This also fixes the Subtotal/GST/Total
            // Charged not reconciling whenever this order (from a session) also
            // billed food alongside the bar items.
            var dispFoodGst = foodTotal * (MASTER_FOOD_GST_RATE / 100);
            var dispBevGst  = Math.max(0, gstAmt - dispFoodGst);

            var footerRows = '<tr><td colspan="3" class="text-end pe-2 text-muted small">Subtotal</td>'
                + '<td class="text-end text-nowrap small">Rs ' + (liquorTotal + foodTotal).toFixed(2) + '</td></tr>';
            if (discountAmt > 0) {
                footerRows += '<tr><td colspan="3" class="text-end pe-2 text-success small"><i class="fa-solid fa-tag me-1"></i>Offer Savings</td>'
                    + '<td class="text-end text-nowrap text-success small">- Rs ' + discountAmt.toFixed(2) + '</td></tr>';
            }
            if (foodTotal > 0) {
                footerRows += '<tr><td colspan="3" class="text-end pe-2 text-muted small">Food GST (' + MASTER_FOOD_GST_RATE + '%)</td>'
                    + '<td class="text-end text-nowrap small">Rs ' + dispFoodGst.toFixed(2) + '</td></tr>';
            }
            footerRows += '<tr><td colspan="3" class="text-end pe-2 text-muted small">Beverage GST (' + MASTER_BEVERAGE_GST_RATE + '%)</td>'
                + '<td class="text-end text-nowrap small">Rs ' + dispBevGst.toFixed(2) + '</td></tr>'
                + '<tr class="fw-bold"><td colspan="3" class="text-end pe-2 fs-6">Total Charged</td>'
                + '<td class="text-end fs-6 text-primary text-nowrap">Rs ' + netAmt.toFixed(2) + '</td></tr>';

            var html = '<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">'
                + '<div>'
                +   '<div class="fw-bold">Order: ' + o.order_no + '</div>'
                +   '<div class="text-muted small">' + new Date(o.created_at).toLocaleDateString('en-IN', {day:'2-digit',month:'2-digit',year:'numeric'}) + ' ' + new Date(o.created_at).toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'}) + '</div>'
                + '</div>'
                + '<div class="text-end">'
                +   '<div class="fw-semibold">' + (o.member ? o.member.name : '—') + '</div>'
                +   '<span class="badge border rounded-pill px-3 py-1 ' + (o.status === 'delivered' ? 'bg-primary-subtle text-primary border-primary' : o.status === 'cancelled' ? 'bg-danger-subtle text-danger border-danger' : 'bg-warning-subtle text-warning border-warning') + '">' + statusLabel + '</span>'
                + '</div></div>'
                + '<div class="table-responsive"><table class="table table-sm align-middle border rounded-3 overflow-hidden">'
                + '<thead><tr style="background:#97A0AC;color:#fff;">'
                + '<th style="padding:8px;">Item</th>'
                + '<th class="text-center" style="padding:8px;">Volume / Qty</th>'
                + '<th class="text-end" style="padding:8px;">Unit Price</th>'
                + '<th class="text-end" style="padding:8px;">Amount</th>'
                + '</tr></thead><tbody>' + rows + '</tbody>'
                + '<tfoot>' + footerRows + '</tfoot>'
                + '</table></div>';

            $('#viewBarOrderBody').html(html);
        }).fail(function () {
            $('#viewBarOrderBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

});
</script>
@endsection
