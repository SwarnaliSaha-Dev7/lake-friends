<div class="left-panel py-5 pb-3 ps-3">
    <div class="logo mb-lg-5 mb-3 text-center pe-3">
        <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="97"
                height="106"></a>
    </div>
    <nav class="nav-menu">
        <ul class="list-unstyled">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}"><i class="fa-solid fa-table-cells"></i> Dashboard</a></li>
            <li><a href="javascript:void(0)"><i class="fa-solid fa-user-gear"></i> Master Manage</a>
                <ul class="list-unstyled" style="{{ request()->routeIs('manage-*') ? 'display: block;' : 'display: none;' }}">
                    <li class="{{ request()->routeIs('manage-operators.*') ? 'active' : '' }}"><a href="{{ route('manage-operators.index') }}">Operator</a></li>
                    {{-- <li><a href="#">Drinks</a></li>
                    <li><a href="#">Item1</a></li>
                    <li><a href="#">Item2</a></li> --}}
                </ul>
            </li>
            <li class="{{ request()->routeIs('club-member.list') ? 'active' : '' }}"><a href="{{ route('club-member.list') }}"><i class="fa-regular fa-user"></i> Club Member</a></li>
            <li><a href="swimming-member.html"><i class="fa-regular fa-user"></i> Swimming Member</a></li>
            <li><a href="#"><i class="fa-regular fa-regular fa-credit-card"></i> Card Manage</a></li>
            <li><a href="liqueur-manage.html"><i class="fa-solid fa-wine-bottle"></i> Liqueur Manage</a>
                <ul class="list-unstyled">
                    <li><a href="current-stock-inventory.html">Current Stock Inventory</a></li>
                    <li><a href="#">Liquor Stock Report</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="fa-solid fa-utensils"></i> Food Manage</a></li>
        </ul>
    </nav>
    <div class="card-entry mt-5">
        <a href="#" class="d-flex align-items-center gap-3 fw-medium ps-2" data-bs-toggle="modal"
            data-bs-target="#cardentryswipe">
            <span
                class="d-inline-flex align-items-center justify-content-center rounded-circle border border-warning"><img
                    src="{{ asset('assets/images/card-hand.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="35"
                    height="35"></span>
            Card entry logs
        </a>
    </div>
</div>
