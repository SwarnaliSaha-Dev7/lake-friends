<div class="left-panel py-5 pb-3 ps-3">
    <div class="logo mb-lg-5 mb-3 text-center pe-3">
        <a href="index.html"><img src="{{ asset('assets/images/logo.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="97"
                height="106"></a>
    </div>
    <nav class="nav-menu">
        <ul class="list-unstyled">
            <li class="active"><a href="index.html"><i class="fa-solid fa-table-cells"></i> Dashboard</a></li>
            <li><a href="club-member.html"><i class="fa-regular fa-user"></i> Club Member</a></li>
            <li><a href="{{ route('manage-membership-duration-types.index') }}"><i class="fa-regular fa-user"></i> Membership Duration Types</a></li>
            <li><a href="swimming-member.html"><i class="fa-regular fa-user"></i> Swimming Member</a></li>
            <li><a href="{{ route('manage-card-types.index') }}"><i class="fa-regular fa-regular fa-credit-card"></i> Card Types</a></li>
            <li><a href="{{ route('manage-cards.index') }}"><i class="fa-regular fa-regular fa-credit-card"></i> Card Manage</a></li>
            <li><a href="{{ route('manage-gst-rates.index') }}"><i class="fa-regular fa-regular fa-credit-card"></i> GST Rate</a></li>
            <li><a href="#"><i class="fa-solid fa-wine-bottle"></i> Liquor Manage</a></li>
            <li><a href="#"><i class="fa-solid fa-utensils"></i> Food Manage</a></li>
        </ul>
    </nav>
    <div class="card-entry position-absolute">
        <a href="#" class="d-flex align-items-center gap-3 fw-medium ps-2" data-bs-toggle="modal"
            data-bs-target="#cardentry">
            <span
                class="d-inline-flex align-items-center justify-content-center rounded-circle border border-warning"><img
                    src="{{ asset('assets/images/card-hand.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="35"
                    height="35"></span>
            Card entry logs
        </a>
    </div>
</div>
