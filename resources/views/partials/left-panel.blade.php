<div class="left-panel py-5 pb-3 ps-3">
    <div class="logo mb-lg-5 mb-3 text-center pe-3">
        <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="97"
                height="106"></a>
    </div>
    <nav class="nav-menu">
        <ul class="list-unstyled">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}"><i class="fa-solid fa-table-cells"></i> Dashboard</a></li>

            @role('admin')
            <li><a href="javascript:void(0)"><i class="fa-solid fa-user-gear"></i> Master Manage</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('manage-*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('manage-operators.*') ? 'active' : '' }}"><a href="{{ route('manage-operators.index') }}">Operator</a></li>
                    <li class="{{ request()->routeIs('manage-gst-rates.*') ? 'active' : '' }}"><a href="{{ route('manage-gst-rates.index') }}">GST Rate</a></li>
                    <!-- <li class="{{ request()->routeIs('manage-fine-rules.*') ? 'active' : '' }}"><a href="{{ route('manage-fine-rules.index') }}">Fine Rules</a></li> -->
                    <li class="{{ request()->routeIs('manage-minimum-spend-rules.*') ? 'active' : '' }}"><a href="{{ route('manage-minimum-spend-rules.index') }}">Minimum Spend Rules</a></li>
                    <li class="{{ request()->routeIs('manage-food-categories.*') ? 'active' : '' }}"><a href="{{ route('manage-food-categories.index') }}">Food Categories</a></li>
                    <li class="{{ request()->routeIs('manage-liquor-categories.*') ? 'active' : '' }}"><a href="{{ route('manage-liquor-categories.index') }}">Liquor Categories</a></li>
                    <li class="{{ request()->routeIs('manage-lockers.*') ? 'active' : '' }}"><a href="{{ route('manage-lockers.index') }}">Locker</a></li>


                    {{-- <li><a href="#">Drinks</a></li>
                    <li><a href="#">Item1</a></li>
                    <li><a href="#">Item2</a></li> --}}
                </ul>
            </li>
            @endrole
            {{-- <li><a href="{{ route('manage-membership-duration-types.index') }}"><i class="fa-regular fa-user"></i> Membership Duration Types</a></li> --}}
            {{-- <li><a href="{{ route('manage-card-types.index') }}"><i class="fa-regular fa-regular fa-credit-card"></i> Card Types</a></li> --}}


            <li class="{{ request()->routeIs('club-member.list') ? 'active' : '' }}"><a href="{{ route('club-member.list') }}"><i class="fa-regular fa-user"></i> Club Member</a></li>
            <li class="{{ request()->routeIs('swimming-member.list') ? 'active' : '' }}"><a href="{{ route('swimming-member.list') }}"><i class="fa-regular fa-user"></i> Swimming Member</a></li>
            @role('admin')
            <li class="{{ request()->routeIs('manage-cards.index') ? 'active' : '' }}"><a href="{{ route('manage-cards.index') }}"><i class="fa-regular fa-regular fa-credit-card"></i> Card Manage</a></li>
            @endrole

            <li class="{{ request()->routeIs('manage-food-items.*') ? 'active' : '' }}"><a href="{{ route('manage-food-items.index') }}"><i class="fa-solid fa-utensils"></i> Food Manage</a></li>

            <li><a href="javascript:void(0)"><i class="fa-solid fa-wine-bottle"></i> Liquor Manage</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('manage-liquor-items.*') || request()->routeIs('liquor-servings.*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('manage-liquor-items.*') ? 'active' : '' }}"><a href="{{ route('manage-liquor-items.index') }}">Liquor Items</a></li>
                    <li class="{{ request()->routeIs('liquor-servings.*') ? 'active' : '' }}"><a href="{{ route('liquor-servings.index') }}">Liquor Menu</a></li>
                </ul>
            </li>

            <li><a href="javascript:void(0)"><i class="fa-solid fa-warehouse"></i> Liquor Stock</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('godown-stock.*') || request()->routeIs('bar-stock.*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('godown-stock.index') ? 'active' : '' }}"><a href="{{ route('godown-stock.index') }}">Godown Stock List</a></li>
                    <li class="{{ request()->routeIs('godown-stock.report') ? 'active' : '' }}"><a href="{{ route('godown-stock.report') }}">Godown Report</a></li>
                    <li class="{{ request()->routeIs('bar-stock.index') ? 'active' : '' }}"><a href="{{ route('bar-stock.index') }}">Bar Stock List</a></li>
                    <li class="{{ request()->routeIs('bar-stock.report') ? 'active' : '' }}"><a href="{{ route('bar-stock.report') }}">Bar Report</a></li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('manage-offers.*') ? 'active' : '' }}"><a href="{{ route('manage-offers.index') }}"><i class="fa-solid fa-tag"></i> Offer Manage</a></li>

            <li><a href="javascript:void(0)"><i class="fa-brands fa-first-order"></i> Order</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('restaurant-orders.*') || request()->routeIs('food-report.*') || request()->routeIs('order-sessions.*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('order-sessions.*') ? 'active' : '' }}">
                        <a href="{{ route('order-sessions.index') }}">Order Sessions</a>
                    </li>
                    <li class="{{ request()->routeIs('restaurant-orders.history') ? 'active' : '' }}">
                        <a href="{{ route('restaurant-orders.history') }}">Order History</a>
                    </li>
                    <li class="{{ request()->routeIs('food-report.*') ? 'active' : '' }}">
                        <a href="{{ route('food-report.index') }}">Food Report</a>
                    </li>
                </ul>
            </li>

            <li><a href="javascript:void(0)"><i class="fa-solid fa-wine-bottle"></i> Bar Order</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('bar-orders.*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('bar-orders.index') ? 'active' : '' }}">
                        <a href="{{ route('bar-orders.index') }}">Today's Orders</a>
                    </li>
                    <li class="{{ request()->routeIs('bar-orders.history') ? 'active' : '' }}">
                        <a href="{{ route('bar-orders.history') }}">Order History</a>
                    </li>
                </ul>
            </li>

            <li><a href="javascript:void(0)"><i class="fa-solid fa-user-gear"></i> Approval</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('memberActionApproval.*') || request()->routeIs('foodItemPriceApproval.*') || request()->routeIs('offerApproval.*') || request()->routeIs('liquorApproval.*') || request()->routeIs('godownStockApproval.*') || request()->routeIs('barStockApproval.*') || request()->routeIs('liquorServingApproval.*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('memberActionApproval.*') ? 'active' : '' }}"><a href="{{ route('memberActionApproval.list') }}">Members</a></li>
                    <li class="{{ request()->routeIs('foodItemPriceApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('foodItemPriceApproval.list') }}">Food Item</a>
                    </li>
                    <li class="{{ request()->routeIs('offerApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('offerApproval.list') }}">Offer</a>
                    </li>
                    <li class="{{ request()->routeIs('liquorApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('liquorApproval.list') }}">Liquor</a>
                    </li>
                    <li class="{{ request()->routeIs('godownStockApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('godownStockApproval.list') }}">Godown Stock</a>
                    </li>
                    <li class="{{ request()->routeIs('barStockApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('barStockApproval.list') }}">Bar Stock</a>
                    </li>
                    <li class="{{ request()->routeIs('liquorServingApproval.*') ? 'active' : '' }}">
                        <a href="{{ route('liquorServingApproval.list') }}">Liquor Menu</a>
                    </li>
                </ul>
            </li>
            @role('admin')
            <li><a class="{{ request()->routeIs('all-action-approval-list') ? 'active' : '' }}" href="{{ route('all-action-approval-list') }}"><i class="fa-regular fa-regular fa-credit-card"></i> All Approval List</a></li>
            @endrole

            
            
        </ul>
    </nav>
    <div class="card-entry mt-5">
        <a href="#" class="d-flex align-items-center gap-3 fw-medium ps-2" data-bs-toggle="modal"
            data-bs-target="#cardentryswipe" id="openBtn">
            <span
                class="d-inline-flex align-items-center justify-content-center rounded-circle border border-warning"><img
                    src="{{ asset('assets/images/card-hand.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="35"
                    height="35"></span>
            Card punch
        </a>
    </div>
</div>
