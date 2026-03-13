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
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Membership Plan">
                                        <small>
                                            <i class="fa-sharp fa-clock-rotate-left"></i>
                                        </small>
                                    </button> <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Wallet Recharge"><small><i class="fa-solid fa-wallet"></i></small></button> <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"  title="Plan Renewal"><small><i class="fa-solid fa-rotate-right"></i></small></button></small></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- card entry Modal end  -->
    @include('base.scripts')
    @yield('customJS')
</body>

</html>
