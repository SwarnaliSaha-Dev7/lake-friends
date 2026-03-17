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

    <!-- card entry Modal start -->
    <div class="modal fade" id="cardentry" tabindex="-1" aria-labelledby="cardEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="cardEntryModalLabel">Gate Entry Logs</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <table class="table border-0 membership-plan-table" cellspacing="1" cellpadding="1">
                        <tbody>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member’s Name:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberName">Soumen Das</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Club Name:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberClubName">Soumen Das</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member’s Id:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberCode">GH231</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberCardNo">12345abcd</small></td>
                            </tr>
                            {{-- <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small>12345abcd</small></td>
                            </tr> --}}
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Active Plan:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberPlan">Gold</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Plan Expiry Date:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberPlanExpiry">12-31-2006</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Wallet Balance:</small>
                                </td>
                                <td class="pe-3"><small id="cardMemberWallet">₹2,450.00</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card Status:</small>
                                </td>
                                <td class="pe-3"><strong><small id="cardStatus" style="text-transform: capitalize;"></small></strong></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member Status:</small>
                                </td>
                                <td class="pe-3"><strong><small id="memberStatus" style="text-transform: capitalize;"></small></strong></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Action:</small>
                                </td>
                                <td class="pe-3"><strong><small id="action" style="text-transform: capitalize;">
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn gate-membership-btn" title="Membership Plan">
                                        <small>
                                            <i class="fa-sharp fa-clock-rotate-left"></i>
                                        </small>
                                    </button> <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn gate-wallet-btn" title="Wallet Recharge"><small><i class="fa-solid fa-wallet"></i></small></button> <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"  title="Plan Renewal"><small><i class="fa-solid fa-rotate-right"></i></small></button></small></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- card entry Modal end  -->

    <!-- Membership plan Modal start-->
    <div class="modal fade" id="membershipplan" tabindex="-1" aria-labelledby="membershipplanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="membershipplanModalLabel">Membership Plan History</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table border-0 membership-plan-table">
                            <tbody id="membershipPlanTbody">
                                <!-- <tr class="active-member">
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">From Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">To Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Plan Type</small> <br>
                                        <small class="text-black-50">Annual</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Fine</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Price</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle text-end p-3">
                                        <img src="{{ asset('assets/images/active-tag.svg') }}" alt="" width="76" height="67"
                                            style="min-width: 76px;">
                                    </td>
                                </tr>
                                <tr class="expire-member">
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">From Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">To Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Plan Type</small> <br>
                                        <small class="text-black-50">Annual</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Fine</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Price</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <img src="{{ asset('assets/images/expire-tag.svg') }}" alt="" class="position-absolute end-0"
                                            style="top: 10px;" width="73" height="24" style="min-width: 73px;">
                                    </td>
                                </tr> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Membership plan Modal end-->

    <!-- Wallet Recharge Confirmation Modal start-->
    <div class="modal fade" id="confirmRechargeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">

                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Wallet Recharge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to recharge this wallet?
                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-success" id="confirmRechargeBtn">
                        Yes, Recharge
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- Wallet Recharge Confirmation Modal end-->

    @include('base.scripts')
    @yield('customJS')
</body>

</html>
