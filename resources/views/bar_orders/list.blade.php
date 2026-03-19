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

    {{-- Today's bar items table --}}
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
                                <th class="text-white fw-medium text-nowrap">Item</th>
                                <th class="text-white fw-medium text-nowrap">Volume</th>
                                <th class="text-white fw-medium text-nowrap">Unit Price</th>
                                <th class="text-white fw-medium text-nowrap">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasRows = false; $grandTotal = 0; @endphp
                            @foreach($orders as $order)
                                @foreach($order->items->whereIn('unit', ['ml', 'btl']) as $item)
                                    @php
                                        $hasRows    = true;
                                        $isBeer     = $item->unit === 'btl';
                                        $volLabel   = $isBeer
                                            ? $item->quantity . ' BTL'
                                            : (($item->metadata['volume_ml'] ?? '?') . 'ml × ' . $item->quantity);
                                        $grandTotal += $item->total_amount;
                                        $offer      = $offerMap[$item->food_item_id] ?? null;
                                        if ($offer) {
                                            $offerLabel = match($offer['type_slug']) {
                                                'percentage' => $offer['discount_value'] . '% OFF',
                                                'flat'       => 'Rs ' . $offer['discount_value'] . ' OFF',
                                                'b1g1'       => 'Buy ' . $offer['buy_qty'] . ' Get ' . $offer['get_qty'],
                                                default      => $offer['offer_name'],
                                            };
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap fw-medium">{{ $order->order_no }}</td>
                                        <td class="text-nowrap">{{ $order->member->name ?? '—' }}</td>
                                        <td class="text-nowrap text-muted small">{{ $order->created_at->format('h:i A') }}</td>
                                        <td class="text-nowrap">
                                            {{ $item->foodItem->name ?? '—' }}
                                            @if($offer)
                                                <br>
                                                <span class="badge bg-danger rounded-pill px-2" style="font-size:0.65rem;">
                                                    <i class="fa-solid fa-tag me-1"></i>{{ $offerLabel }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $volLabel }}</td>
                                        <td class="text-nowrap">Rs {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-nowrap fw-semibold">Rs {{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$hasRows)
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No bar orders today.</td>
                                </tr>
                            @endif
                        </tbody>
                        @if($hasRows)
                        <tfoot>
                            <tr class="fw-bold" style="background:#f1f3f5;">
                                <td colspan="6" class="text-end pe-3">Total</td>
                                <td class="text-nowrap text-primary">Rs {{ number_format($grandTotal, 2) }}</td>
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
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">GST (<span id="cartGstPct">0</span>%)</span>
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

    {{-- Peg size modal for spirits --}}
    <div class="modal fade" id="pegSizeModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold" id="pegItemName"></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peg Size</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary peg-size-btn" data-ml="30">30 ml</button>
                            <button type="button" class="btn btn-outline-primary peg-size-btn" data-ml="60">60 ml</button>
                            <button type="button" class="btn btn-outline-primary peg-size-btn" data-ml="90">90 ml</button>
                        </div>
                        <div class="mt-2">
                            <input type="number" id="customPegMl" class="form-control shadow-none"
                                placeholder="Custom ml..." min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity <small class="text-muted">(pegs)</small></label>
                        <input type="number" id="pegQty" class="form-control shadow-none" min="1" value="1">
                    </div>
                    <div class="p-2 rounded-3 bg-light small mb-2">
                        Available: <strong id="pegAvailableDisplay"></strong>
                    </div>
                    <button type="button" class="btn btn-primary w-100 fw-semibold" id="confirmPegBtn">
                        Add to Order
                    </button>
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

    var GST_PCT   = 10;
    var barItems  = [];
    var cart      = [];   // [{id, name, is_beer, volume_ml, quantity, deduct_qty, unit_price, total}]
    var currentPegItem = null;
    var selectedPegMl  = 60;

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

        // Sort: in-stock first, low-stock next, out-of-stock last
        var sorted = items.slice().sort(function (a, b) {
            var rank = function (x) { return !x.in_stock ? 2 : (x.is_low ? 1 : 0); };
            return rank(a) - rank(b);
        });

        sorted.forEach(function (item) {
            var isOut = !item.in_stock;
            var isLow = item.is_low;

            var remainder    = item.size_ml > 0 ? item.bar_stock % item.size_ml : item.bar_stock;
            var btlBreakdown = item.btl_eq > 0
                ? ' (' + item.btl_eq + ' BTL' + (remainder > 0 ? ' ' + remainder.toLocaleString() + ' ml' : '') + ')'
                : '';
            var stockDisplay = item.is_beer
                ? item.bar_stock + ' BTL'
                : item.bar_stock.toLocaleString() + ' ml' + btlBreakdown;

            var stockColor  = isOut ? '#dc3545' : (isLow ? '#fd7e14' : '#198754');
            var stockLabel  = isOut ? 'Out of Stock' : (isLow ? 'Low Stock' : 'In Stock');
            var cardBorder  = isOut ? 'border-danger' : (isLow ? 'border-warning' : '');
            var cardBg      = isOut ? 'background:#fff5f5;' : (isLow ? 'background:#fffbf0;' : '');

            var typeBadge = item.is_beer
                ? '<span class="badge bg-warning text-dark" style="font-size:0.65rem;">Beer</span>'
                : '<span class="badge bg-info text-white" style="font-size:0.65rem;">Spirit</span>';
            var sizeTxt = item.size_ml ? item.size_ml + ' ml' : '';

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

            html += '<div class="col-sm-6 col-md-4 bar-item-card" data-name="' + item.name.toLowerCase() + '" data-type="' + (item.is_beer ? 'beer' : 'spirit') + '">'
                + '<div class="border rounded-3 p-2 h-100 d-flex flex-column ' + cardBorder + '" style="font-size:0.82rem;' + cardBg + '">'
                +   '<div class="d-flex align-items-start justify-content-between mb-1">'
                +     '<span class="fw-semibold">' + item.name + '</span>'
                +     typeBadge
                +   '</div>'
                +   '<div class="text-muted mb-1">' + item.category + (sizeTxt ? ' · ' + sizeTxt : '') + '</div>'
                +   '<div class="mb-1 d-flex align-items-center gap-2">'
                +     '<span class="fw-bold" style="color:' + stockColor + ';font-size:0.78rem;">'
                +       '<i class="fa-solid fa-' + (isOut ? 'circle-xmark' : (isLow ? 'triangle-exclamation' : 'circle-check')) + ' me-1"></i>'
                +       stockLabel
                +     '</span>'
                +     '<span class="text-muted" style="font-size:0.75rem;">' + stockDisplay + '</span>'
                +   '</div>'
                +   (offerBadge ? '<div class="mb-1">' + offerBadge + '</div>' : '')
                +   '<div class="fw-bold text-primary mb-2">Rs ' + item.price.toFixed(2) + (item.is_beer ? '/BTL' : '/unit') + '</div>'
                +   '<button type="button" class="btn btn-sm btn-outline-primary mt-auto add-bar-item-btn"'
                +     ' data-id="' + item.id + '"'
                +     ' data-name="' + item.name + '"'
                +     ' data-is-beer="' + (item.is_beer ? '1' : '0') + '"'
                +     ' data-size-ml="' + item.size_ml + '"'
                +     ' data-price="' + item.price + '"'
                +     ' data-stock="' + item.bar_stock + '"'
                +     (item.in_stock ? '' : ' disabled')
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
        var $btn   = $(this);
        var isBeer = $btn.data('is-beer') == '1';
        var id     = $btn.data('id');
        var name   = $btn.data('name');
        var price  = parseFloat($btn.data('price'));
        var stock  = parseInt($btn.data('stock'));
        var sizeMl = parseInt($btn.data('size-ml')) || 0;

        if (isBeer) {
            addToCart({ id: id, name: name, is_beer: true, volume_ml: null, quantity: 1, deduct_qty: 1, unit_price: price, bar_stock: stock });
        } else {
            // Show peg size selector
            currentPegItem = { id: id, name: name, is_beer: false, size_ml: sizeMl, unit_price: price, bar_stock: stock };
            selectedPegMl  = 60;
            $('#pegItemName').text(name + (sizeMl ? ' (' + sizeMl + ' ml)' : ''));
            $('#customPegMl').val('');
            $('#pegQty').val(1);
            var stockMl = stock;
            $('#pegAvailableDisplay').text(stockMl.toLocaleString() + ' ml');
            $('.peg-size-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
            $('.peg-size-btn[data-ml="60"]').removeClass('btn-outline-primary').addClass('btn-primary active');
            $('#pegSizeModal').modal('show');
        }
    });

    // Peg size button selection
    $(document).on('click', '.peg-size-btn', function () {
        $('.peg-size-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
        selectedPegMl = parseInt($(this).data('ml'));
        $('#customPegMl').val('');
    });

    $('#customPegMl').on('input', function () {
        var val = parseInt($(this).val());
        if (val > 0) {
            selectedPegMl = val;
            $('.peg-size-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        }
    });

    $('#confirmPegBtn').on('click', function () {
        var qty      = parseInt($('#pegQty').val()) || 1;
        var ml       = selectedPegMl;
        if (!ml || ml < 1) { toastr.warning('Select a peg size.'); return; }

        var deductQty = qty * ml;
        if (deductQty > currentPegItem.bar_stock) {
            toastr.error('Not enough bar stock. Available: ' + currentPegItem.bar_stock.toLocaleString() + ' ml');
            return;
        }

        addToCart({
            id: currentPegItem.id,
            name: currentPegItem.name,
            is_beer: false,
            volume_ml: ml,
            quantity: qty,
            deduct_qty: deductQty,
            unit_price: currentPegItem.unit_price,
            bar_stock: currentPegItem.bar_stock,
        });
        $('#pegSizeModal').modal('hide');
    });

    function addToCart(item) {
        // Merge if same item + same volume_ml
        var existing = cart.find(function (c) {
            return c.id === item.id && c.volume_ml === item.volume_ml;
        });
        if (existing) {
            var newDeduct = existing.deduct_qty + item.deduct_qty;
            if (newDeduct > item.bar_stock) {
                toastr.error('Not enough bar stock.');
                return;
            }
            existing.quantity  += item.quantity;
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
            var desc = item.is_beer
                ? item.quantity + ' BTL'
                : item.quantity + ' × ' + item.volume_ml + ' ml = ' + item.deduct_qty.toLocaleString() + ' ml';
            var total = item.quantity * item.unit_price;

            html += '<div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">'
                +   '<div class="flex-grow-1">'
                +     '<div class="fw-semibold small">' + item.name + '</div>'
                +     '<div class="text-muted" style="font-size:0.75rem;">' + desc + ' · Rs ' + item.unit_price.toFixed(2) + '/unit</div>'
                +   '</div>'
                +   '<div class="text-end">'
                +     '<div class="fw-bold small">Rs ' + total.toFixed(2) + '</div>'
                +     '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-cart-item" data-idx="' + idx + '" style="font-size:0.7rem;">Remove</button>'
                +   '</div>'
                + '</div>';
        });

        $('#barCartItems').html(html);

        var subtotal = cart.reduce(function (s, c) { return s + c.quantity * c.unit_price; }, 0);
        var gstAmt   = subtotal * GST_PCT / 100;
        var total    = subtotal + gstAmt;

        $('#cartSubtotal').text('Rs ' + subtotal.toFixed(2));
        $('#cartGstPct').text(GST_PCT);
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

        var subtotal = cart.reduce(function (s, c) { return s + c.quantity * c.unit_price; }, 0);
        var gstAmt   = subtotal * GST_PCT / 100;
        var netAmt   = subtotal + gstAmt;

        var items = cart.map(function (c) {
            return {
                food_item_id: c.id,
                is_beer:      c.is_beer,
                volume_ml:    c.volume_ml,
                quantity:     c.quantity,
                deduct_qty:   c.deduct_qty,
                unit_price:   c.unit_price,
                total_amount: c.quantity * c.unit_price,
            };
        });

        var $btn = $('#placeBarOrderBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Placing...');

        $.ajax({
            url: '{{ route("bar-orders.store") }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:          '{{ csrf_token() }}',
                member_id:       memberId,
                items:           items,
                taxable_amount:  subtotal.toFixed(2),
                gst_percentage:  GST_PCT,
                gst_amount:      gstAmt.toFixed(2),
                net_amount:      netAmt.toFixed(2),
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
                    $row.find('.mark-served-btn, .cancel-bar-order-btn').replaceWith(
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
            (o.items || []).filter(function (it) {
                return it.unit === 'ml' || it.unit === 'btl';
            }).forEach(function (it) {
                var isBeer  = it.unit === 'btl';
                var meta    = it.metadata || {};
                var volDesc = isBeer
                    ? '1 BTL × ' + it.quantity
                    : (meta.volume_ml || '?') + ' ml × ' + it.quantity + ' = ' + (it.quantity * (meta.volume_ml || 0)) + ' ml total';
                var amt = parseFloat(it.total_amount);
                liquorTotal += amt;

                rows += '<tr>'
                    + '<td class="fw-medium">' + (it.food_item ? it.food_item.name : '—') + '</td>'
                    + '<td class="text-center text-nowrap">' + volDesc + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + parseFloat(it.unit_price).toFixed(2) + '</td>'
                    + '<td class="text-end text-nowrap">Rs ' + amt.toFixed(2) + '</td>'
                    + '</tr>';
            });

            var statusColors = { paid:'text-success', delivered:'text-primary', cancelled:'text-danger' };
            var statusLabel  = o.status === 'delivered' ? 'Served' : (o.status.charAt(0).toUpperCase() + o.status.slice(1));
            var sClass       = statusColors[o.status] || 'text-muted';

            if (!rows) {
                $('#viewBarOrderBody').html('<p class="text-muted text-center py-4">No liquor items in this order.</p>');
                return;
            }

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
                + '<tfoot><tr class="fw-bold"><td colspan="3" class="text-end pe-2 fs-6">Liquor Total</td>'
                + '<td class="text-end fs-6 text-primary">Rs ' + liquorTotal.toFixed(2) + '</td></tr>'
                + '</tfoot></table></div>';

            $('#viewBarOrderBody').html(html);
        }).fail(function () {
            $('#viewBarOrderBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

});
</script>
@endsection
