{{-- Quick Links Panel --}}
<style>
.ql-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.35);
    z-index: 1040;
    backdrop-filter: blur(2px);
}
.ql-panel {
    position: fixed;
    top: 65px;
    left: 240px;
    right: 0;
    background: #fff;
    border-top: 3px solid #4f46e5;
    box-shadow: 0 12px 40px rgba(0,0,0,.15);
    z-index: 1045;
    max-height: calc(100vh - 75px);
    overflow-y: auto;
    padding: 20px 28px 28px;
}
.ql-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.ql-panel-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ql-panel-header h5 i {
    color: #4f46e5;
}
.ql-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 20px 28px;
}
.ql-section h6 {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding-bottom: 6px;
    margin-bottom: 8px;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.ql-section h6 i {
    color: #4f46e5;
    font-size: 11px;
}
.ql-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.ql-section ul li a {
    font-size: 12.5px;
    color: #475569;
    text-decoration: none;
    display: block;
    padding: 3px 0;
    transition: color .12s;
}
.ql-section ul li a::before {
    content: "» ";
    color: #cbd5e1;
}
.ql-section ul li a:hover {
    color: #4f46e5;
}
.ql-section ul li a:hover::before {
    color: #4f46e5;
}
/* Quick Links toggle button */
#qlToggle {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    cursor: pointer;
    transition: background .15s, color .15s;
    margin-right: 10px;
}
#qlToggle:hover, #qlToggle.active {
    background: #ede9fe;
    color: #4f46e5;
    border-color: #c4b5fd;
}
#qlToggle i { font-size: 14px; }
</style>

<div id="qlOverlay" class="ql-overlay" style="display:none;"></div>

<div id="qlPanel" class="ql-panel" style="display:none;">
    <div class="ql-panel-header">
        <h5><i class="fa-solid fa-grip"></i> Quick Links</h5>
        <button id="qlClose" class="btn-close" aria-label="Close"></button>
    </div>

    <div class="ql-grid">

        {{-- Members --}}
        <div class="ql-section">
            <h6><i class="fa-regular fa-user"></i> Members</h6>
            <ul>
                <li><a href="{{ route('club-member.list') }}">Club Member</a></li>
                <li><a href="{{ route('swimming-member.list') }}">Swimming Member</a></li>
                @role('admin')
                <li><a href="{{ route('manage-cards.index') }}">Card Manage</a></li>
                @endrole
            </ul>
        </div>

        {{-- Orders --}}
        <div class="ql-section">
            <h6><i class="fa-brands fa-first-order"></i> Orders</h6>
            <ul>
                <li><a href="{{ route('order-sessions.index') }}">Current Order</a></li>
                <li><a href="{{ route('restaurant-orders.history') }}">Order History</a></li>
                <li><a href="{{ route('cancelled-bills.index') }}">Cancelled Bills</a></li>
                <li><a href="{{ route('food-report.index') }}">Food Report</a></li>
            </ul>
        </div>

        {{-- Bar Orders --}}
        <div class="ql-section">
            <h6><i class="fa-solid fa-wine-bottle"></i> Bar Orders</h6>
            <ul>
                <li><a href="{{ route('bar-orders.index') }}">Today's Orders</a></li>
                <li><a href="{{ route('bar-orders.history') }}">Order History</a></li>
            </ul>
        </div>

        {{-- Food & Liquor --}}
        <div class="ql-section">
            <h6><i class="fa-solid fa-utensils"></i> Food & Liquor</h6>
            <ul>
                <li><a href="{{ route('manage-food-items.index') }}">Food Items</a></li>
                <li><a href="{{ route('manage-liquor-items.index') }}">Liquor Items</a></li>
                <li><a href="{{ route('liquor-servings.index') }}">Liquor Menu</a></li>
            </ul>
        </div>

        {{-- Stock --}}
        <div class="ql-section">
            <h6><i class="fa-solid fa-warehouse"></i> Stock</h6>
            <ul>
                <li><a href="{{ route('godown-stock.index') }}">Godown List</a></li>
                <li><a href="{{ route('godown-stock.report') }}">Godown Report</a></li>
                <li><a href="{{ route('bar-stock.index') }}">Bar Stock List</a></li>
                <li><a href="{{ route('bar-stock.report') }}">Bar Report</a></li>
            </ul>
        </div>

        {{-- Offers --}}
        <div class="ql-section">
            <h6><i class="fa-solid fa-tag"></i> Offers</h6>
            <ul>
                <li><a href="{{ route('manage-offers.index') }}">Offer Manage</a></li>
            </ul>
        </div>

        {{-- Approval --}}
        <div class="ql-section">
            <h6><i class="fa-solid fa-circle-check"></i> Approval</h6>
            <ul>
                <li><a href="{{ route('memberActionApproval.list') }}">Members</a></li>
                <li><a href="{{ route('foodItemPriceApproval.list') }}">Food Item</a></li>
                <li><a href="{{ route('offerApproval.list') }}">Offer</a></li>
                <li><a href="{{ route('liquorApproval.list') }}">Liquor</a></li>
                <li><a href="{{ route('godownStockApproval.list') }}">Godown Stock</a></li>
                <li><a href="{{ route('barStockApproval.list') }}">Bar Stock</a></li>
                <li><a href="{{ route('liquorServingApproval.list') }}">Liquor Menu</a></li>
                @role('admin')
                <li><a href="{{ route('all-action-approval-list') }}">All Approvals</a></li>
                @endrole
            </ul>
        </div>

        {{-- Master Manage (admin only) --}}
        @role('admin')
        <div class="ql-section">
            <h6><i class="fa-solid fa-user-gear"></i> Master Manage</h6>
            <ul>
                <li><a href="{{ route('manage-operators.index') }}">Operators</a></li>
                <li><a href="{{ route('manage-gst-rates.index') }}">GST Rate</a></li>
                <li><a href="{{ route('manage-fine-rules.index') }}">Fine Rules</a></li>
                <li><a href="{{ route('manage-minimum-spend-rules.index') }}">Min Spend Rules</a></li>
                <li><a href="{{ route('manage-food-categories.index') }}">Food Categories</a></li>
                <li><a href="{{ route('manage-liquor-categories.index') }}">Liquor Categories</a></li>
                <li><a href="{{ route('manage-lockers.index') }}">Lockers</a></li>
            </ul>
        </div>
        @endrole

    </div>
</div>

<script>
(function () {
    var $btn     = document.getElementById('qlToggle');
    var $panel   = document.getElementById('qlPanel');
    var $overlay = document.getElementById('qlOverlay');
    var $close   = document.getElementById('qlClose');

    function openQL()  { $panel.style.display = 'block'; $overlay.style.display = 'block'; $btn.classList.add('active'); }
    function closeQL() { $panel.style.display = 'none';  $overlay.style.display = 'none';  $btn.classList.remove('active'); }

    $btn.addEventListener('click', function () {
        $panel.style.display === 'none' ? openQL() : closeQL();
    });
    $overlay.addEventListener('click', closeQL);
    $close.addEventListener('click', closeQL);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeQL();
    });
})();
</script>
