<!-- All Links -->
<script src="{{ asset('assets/js/jquery.min.js') }} "></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/edit-js.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>

<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000"
    };
    @if (Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
    @endif
    @if (Session::has('error'))
        toastr.error("{{ Session::get('error') }}");
    @endif

    // alert('hi');
    const openBtn = document.getElementById("openBtn");
    const cardInput = document.getElementById("cardInput");

    openBtn.addEventListener("click", () => {
        // alert('hi');
        cardInput.value = "";
        setTimeout(() => cardInput.focus(), 200);
        // cardInput.focus();
    });

    openBtn.addEventListener("keydown", (e) => {
        if (e.key === "Enter") e.preventDefault();
    });

    cardInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            const cardNo = cardInput.value.trim();
            if(cardNo){
                $('.swipe-animation').hide();
                $('#cardLoader').show();
                // resultText.innerHTML = "Your card no is: " + cardNo;
                // overlay.style.display = "none";
                // alert("Your card no is: " + cardNo);
                cardInput.value = "";
                // $('#cardentryswipe').modal('hide');
                $.ajax({
                    url: '{{route("getMemberDetails", ":cardNo")}}'.replace(':cardNo', cardNo),
                    type: 'GET',
                    success: function(response){
                        if (response.statusCode == 200) {
                            $('#cardentryswipe').modal('hide');
                            console.log(response);
                            if (response.status) {
                                toastr.error('Card is ' + response.status);
                            }
                            const statusMap = {
                                'active': 'Active',
                                'pending': 'Pending',
                                'rejected': 'Rejected',
                            };
                            let statusCode = response.data.status; // e.g., "pending_approval"
                            let humanStatus = statusMap[statusCode]
                            var memberName = response.data.name;
                            $('#cardMemberName').text(memberName);
                            $('#cardMemberClubName').text(response.data.club_details.name);
                            $('#cardMemberCode').text(response.data.member_code);
                            $('#cardMemberCardNo').text(response.data.card_details.card_no);
                            var today = new Date(); today.setHours(0,0,0,0);
                            var allPlans = response.data.purchase_history || [];

                            // Separate upcoming (start_date > today) from current (start_date <= today)
                            var upcomingPlan = null, activePlan = null;
                            for (var pi = 0; pi < allPlans.length; pi++) {
                                var p = allPlans[pi];
                                var pStart = p.start_date ? new Date(p.start_date) : null;
                                if (pStart) pStart.setHours(0,0,0,0);
                                if (pStart && pStart > today) {
                                    if (!upcomingPlan) upcomingPlan = p;
                                } else {
                                    if (!activePlan) activePlan = p;
                                }
                                if (upcomingPlan && activePlan) break;
                            }
                            var latestPlan = activePlan || allPlans[0];

                            $('#cardMemberPlan').text(latestPlan?.membership_plan_type?.name ?? '—');
                            var expiryDate = latestPlan?.expiry_date ?? null;
                            var isPlanExpired = false;
                            if (expiryDate) {
                                var expiryObj = new Date(expiryDate); expiryObj.setHours(0,0,0,0);
                                isPlanExpired = expiryObj < today;
                                var formatted = expiryObj.toLocaleDateString('en-IN');
                            } else {
                                var formatted = '—';
                            }

                            // Style Plan Expiry card
                            var $expiryCard = $('#cardMemberPlanExpiry').closest('.rounded-3');
                            if (isPlanExpired) {
                                $expiryCard.css('background', '#fee2e2');
                                $('#cardMemberPlanExpiry').html(
                                    '<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>' + formatted + '</span>'
                                );
                            } else {
                                $expiryCard.css('background', '#f8f9fa');
                                $('#cardMemberPlanExpiry').text(formatted);
                            }

                            // Upcoming renewal plan
                            if (upcomingPlan) {
                                var upStartObj = new Date(upcomingPlan.start_date);
                                $('#cardUpcomingPlanName').text(upcomingPlan.membership_plan_type?.name ?? '—');
                                $('#cardUpcomingPlanStart').text(upStartObj.toLocaleDateString('en-IN'));
                                $('#upcomingPlanRow').removeClass('d-none');
                            } else {
                                $('#upcomingPlanRow').addClass('d-none');
                            }

                            var walletDetails = response.data.wallet_details;
                            var walletBal = parseFloat(walletDetails && walletDetails.current_balance ? walletDetails.current_balance : 0).toFixed(2);
                            $('#cardMemberWallet').text('Rs.' + walletBal);

                            // Avatar image (fallback to initials)
                            var nameParts = memberName.trim().split(' ');
                            var initials = '';
                            for (var ni = 0; ni < Math.min(nameParts.length, 2); ni++) {
                                if (nameParts[ni]) initials += nameParts[ni][0].toUpperCase();
                            }

                            let img_base_url = "{{ url('/') }}";

                            var avatarUrl = response.data.image
                            ? img_base_url + '/' + response.data.image.replace(/^\/+/, '')
                            : '';

                            // var avatarUrl = response.data.image
                            //     ? '/' + response.data.image.replace(/^\/+/, '')
                            //     : '';

                            if (avatarUrl) {
                                $('#cardMemberAvatar')
                                    .css({ 'background-image': 'none' })
                                    .html('<img src="' + avatarUrl + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">');
                            } else {
                                $('#cardMemberAvatar')
                                    .text(initials)
                                    .empty()
                                    .text(initials)
                                    .css({ 'background-image': 'none' });
                            }

                            // Card status badge
                            var cardStatusVal = response.cardStatus || '';
                            var cardBadgeClass = cardStatusVal === 'active'
                                ? 'bg-success-subtle text-success border-success'
                                : 'bg-danger-subtle text-danger border-danger';
                            var cardStatusLabel = cardStatusVal ? (cardStatusVal.charAt(0).toUpperCase() + cardStatusVal.slice(1)) : 'N/A';
                            $('#cardStatusBadge').removeClass().addClass('badge rounded-pill px-3 py-1 border ' + cardBadgeClass)
                                .text('Card : ' + cardStatusLabel);
                            // Member status badge — override with Plan Expired if applicable
                            if (isPlanExpired) {
                                $('#memberStatusBadge').removeClass()
                                    .addClass('badge rounded-pill px-3 py-1 border bg-danger text-white border-danger')
                                    .html('<i class="fa-solid fa-triangle-exclamation me-1"></i>Plan: Expired');
                            } else {
                                var memberBadgeClass = statusCode === 'active'
                                    ? 'bg-success-subtle text-success border-success'
                                    : (statusCode === 'pending'
                                        ? 'bg-warning-subtle text-warning border-warning'
                                        : 'bg-danger-subtle text-danger border-danger');
                                $('#memberStatusBadge').removeClass().addClass('badge rounded-pill px-3 py-1 border ' + memberBadgeClass)
                                    .text('Plan: ' + (humanStatus || 'N/A'));
                            }

                            // Member type
                            var memberTypeName = response.data.member_details?.membership_type?.name ?? '—';
                            $('#cardMemberType').text(memberTypeName);
                            $('#cardMemberTypeCard').text(memberTypeName);

                            // Store member ID & member type for action buttons
                            var memberTypeSlug = response.data.member_details?.membership_type?.name?.toLowerCase().includes('swim') ? 'swimming' : 'club';
                            $('#cardentry').data('member-id', response.data.id);
                            $('#cardentry').data('member-type', memberTypeSlug);

                            // Show/hide elements based on member type
                            if (memberTypeSlug === 'swimming') {
                                $('#renewalBtn').addClass('d-none');
                                $('#swimRenewalBtn').removeClass('d-none');
                                $('#createOrderBtn').addClass('d-none');
                                $('#cardWalletSection').addClass('d-none');
                                $('#transactionHistoryBtn').addClass('d-none');
                            } else {
                                $('#renewalBtn').removeClass('d-none');
                                $('#swimRenewalBtn').addClass('d-none');
                                $('#createOrderBtn').removeClass('d-none');
                                $('#cardWalletSection').removeClass('d-none');
                                $('#transactionHistoryBtn').removeClass('d-none');
                            }

                            $('#cardentry').modal('show');

                            // Show expired warning toastr after modal opens
                            if (isPlanExpired) {
                                setTimeout(function() {
                                    toastr.warning(
                                        '"' + memberName + '" — membership plan expired on ' + formatted + '. Please renew.',
                                        'Plan Expired',
                                        { timeOut: 6000 }
                                    );
                                }, 400);
                            }

                            $('.swipe-animation').show();
                            $('#cardLoader').hide();
                            // $('.spinner-border').replaceWith(originalBtn);
                            // $('#viewprofile').modal('show');
                        }
                        else{
                            $('#cardLoader').hide();
                            $('.swipe-animation').show();
                            toastr.error(response.error || 'Something Went Wrong.');
                            console.log(response);
                        }

                    },
                    error: function(){
                        $('#cardLoader').hide();
                        $('.swipe-animation').show();
                        toastr.error('Something Went Wrong.');
                    }
                });
                // $('#cardentry').modal('show');
            }
        }
    });

    // Keep focus on hidden input
    cardInput.addEventListener("blur", () => {
        if($('#cardentryswipe').hasClass('show')){
            setTimeout(()=>cardInput.focus(),100);
        }
    });

    $(document).on('click', '#cardentry .gate-membership-btn', function () {

        let memberId = $('#cardentry').data('member-id');
        let memberType = $('#cardentry').data('member-type');

        // console.log(memberId);
        // console.log(memberType);

        if (!memberId || !memberType) {
        toastr.error('Member data missing');
        return;
    }

        let url = '';

        if (memberType === 'club') {
            url = '{{route("club-member.membership-plan", ":id")}}'.replace(':id', memberId);
        }
        else if (memberType === 'swimming') {
            url = '{{route("swimming-member.membership-plan", ":id")}}'.replace(':id', memberId);
        }
        else {
            toastr.error('Invalid member type');
            return;
        }

        loadMembershipPlan(url);

    });

    function loadMembershipPlan(url) {

        let tbody = $('#membershipPlanTbody');

        // Show loading state
        tbody.html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: url,
            type: 'GET',

            success: function(response){

                if (response.statusCode == 200) {

                    tbody.empty();

                    response.data.forEach(function(plan) {

                        let fromDate = new Date(plan.start_date).toLocaleDateString('en-IN');
                        let toDate = new Date(plan.expiry_date).toLocaleDateString('en-IN');

                        let today = new Date();
                        let expiryDate = new Date(plan.expiry_date);

                        let isActive = expiryDate >= today;
                        if (plan.status == 'cancelled') isActive = 0;

                        let row = `
                            <tr>
                                <td class="bg-info p-3">${fromDate}</td>
                                <td class="bg-info p-3">${toDate}</td>
                                <td class="bg-info p-3">${plan.membership_plan_type.name}</td>
                                <td class="bg-info p-3">Rs ${plan.fine_amount}</td>
                                <td class="bg-info p-3">Rs ${plan.net_amount}</td>
                                <td class="bg-info text-end p-3">
                                    <img src="{{ asset('assets/images') }}${isActive ? '/active-tag.svg' : '/expire-tag.svg'}">
                                </td>
                            </tr>
                        `;

                        tbody.append(row);
                    });

                    //  Close first modal
                    $('#cardentry').modal('hide');

                    //  Open membership modal
                    setTimeout(() => {
                        $('#membershipplan').modal('show');
                    }, 300);

                } else {
                    toastr.error('Failed to load membership plans');
                }
            },

            error: function(){
                toastr.error('Something Went Wrong.');
            }
        });
    }

    $(document).on('click', '#cardentry .gate-wallet-btn', function () {

        let memberId = $('#cardentry').data('member-id');
        let memberType = $('#cardentry').data('member-type');

        if (!memberId || !memberType) {
            toastr.error('Member data missing');
            return;
        }

        let url = '';

        if (memberType === 'club') {
            url = '{{route("club-member.fetch-wallet-balance", ":id")}}'.replace(':id', memberId);
        }
        else if (memberType === 'swimming') {
            url = '{{route("swimming-member.fetch-wallet-balance", ":id")}}'.replace(':id', memberId);
        }
        else {
            toastr.error('Invalid member type');
            return;
        }

        loadWalletData(url, memberId, memberType, { keepCardEntry: false });
    });

    /* Card punch popup → Wallet Recharge button */
    $('#walletRechargeBtn').on('click', function () {
        let memberId = $('#cardentry').data('member-id');
        let memberType = $('#cardentry').data('member-type');

        if (!memberId || !memberType) {
            toastr.error('Member data missing');
            return;
        }

        let url = '';
        if (memberType === 'club') {
            url = '{{route("club-member.fetch-wallet-balance", ":id")}}'.replace(':id', memberId);
        } else if (memberType === 'swimming') {
            url = '{{route("swimming-member.fetch-wallet-balance", ":id")}}'.replace(':id', memberId);
        } else {
            toastr.error('Invalid member type');
            return;
        }

        loadWalletData(url, memberId, memberType, { keepCardEntry: true });
    });

    /* Card punch popup → Transaction History button */
    $('#transactionHistoryBtn').on('click', function () {
        let memberId   = $('#cardentry').data('member-id');
        let memberType = $('#cardentry').data('member-type');
        let memberName = $('#cardMemberName').text() || '—';

        if (!memberId || !memberType) { toastr.error('Member data missing'); return; }

        let container = $('#memberTransactionHistoryTbody');
        $('#txnHistoryMemberName').text(memberName);
        container.html('<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>');

        let url = memberType === 'club'
            ? '{{route("club-member.member-ledger", ":id")}}'.replace(':id', memberId)
            : '{{route("swimming-member.member-ledger", ":id")}}'.replace(':id', memberId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.statusCode == 200) {
                    container.empty();

                    if (response.data.length > 0) {
                        response.data.forEach(function(entry, index, arr) {
                            let purposeRaw = (entry.purpose || '').toLowerCase();
                            let purpose    = (entry.purpose || '-').toString().replace(/_/g, ' ').replace(/\b\w/g, m => m.toUpperCase());
                            let direction  = entry.direction || 'debit';
                            let isCredit   = direction === 'credit';
                            let sign       = isCredit ? '+' : '-';
                            let amtColor   = isCredit ? '#16a34a' : '#dc2626';
                            let maker      = entry.maker || null;
                            let remarks    = entry.remarks || '';
                            let createdAt  = entry.created_at
                                ? new Date(entry.created_at).toLocaleString('en-IN', { timeZone: 'Asia/Kolkata' })
                                : '-';

                            // Icon based on transaction type
                            let icon, iconBg, iconColor;
                            if (purposeRaw.includes('recharge') || (isCredit && purposeRaw.includes('wallet'))) {
                                icon = 'fa-wallet';      iconBg = '#dcfce7'; iconColor = '#16a34a';
                            } else if (purposeRaw.includes('membership') || purposeRaw.includes('renewal') || purposeRaw.includes('plan')) {
                                icon = 'fa-id-badge';    iconBg = '#f3e8ff'; iconColor = '#7c3aed';
                            } else if (purposeRaw.includes('food') || purposeRaw.includes('restaurant')) {
                                icon = 'fa-utensils';    iconBg = '#fff7ed'; iconColor = '#ea580c';
                            } else if (purposeRaw.includes('bar') || purposeRaw.includes('liquor') || purposeRaw.includes('order')) {
                                icon = 'fa-wine-bottle'; iconBg = '#eff6ff'; iconColor = '#2563eb';
                            } else if (isCredit) {
                                icon = 'fa-arrow-down';  iconBg = '#dcfce7'; iconColor = '#16a34a';
                            } else {
                                icon = 'fa-arrow-up';    iconBg = '#fee2e2'; iconColor = '#dc2626';
                            }

                            let borderBottom = index < arr.length - 1 ? 'border-bottom:1px solid #e9ecef;' : '';

                            let row = `
                                <div class="d-flex align-items-center gap-3 px-4 py-3" style="background:#fff;${borderBottom}">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width:38px;height:38px;background:${iconBg};">
                                        <i class="fa-solid ${icon}" style="font-size:14px;color:${iconColor};"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-dark lh-sm" style="font-size:13px;">${purpose}</div>
                                        ${maker ? `<div class="text-muted" style="font-size:11px;">By: ${maker}</div>` : ''}
                                        <div class="text-muted" style="font-size:11px;">${createdAt}</div>
                                        ${remarks ? `<div class="text-muted" style="font-size:11px;">Remarks: ${remarks}</div>` : ''}
                                    </div>
                                    <div class="fw-bold flex-shrink-0 ms-2" style="font-size:14px;color:${amtColor};">${sign}₹${parseFloat(entry.amount).toFixed(2)}</div>
                                </div>`;

                            container.append(row);
                        });
                    } else {
                        container.html('<div class="text-center text-muted py-5" style="font-size:13px;"><i class="fa-solid fa-inbox d-block mb-2 fs-4 opacity-40"></i>No transactions found</div>');
                    }

                    setTimeout(() => { $('#memberTransactionHistoryModal').modal('show'); }, 300);
                } else {
                    toastr.error('Failed to load transaction history');
                }
            },
            error: function() { toastr.error('Something Went Wrong.'); }
        });
    });

    /* Quick amount select buttons in wallet recharge modal */
    $(document).on('click', '.quick-amt-btn', function () {
        $('#amountInput').val($(this).data('amt'));
    });

    function loadWalletData(url, memberId, memberType, opts) {
        opts = opts || {};
        var keepCardEntry = opts.keepCardEntry === true;

        let tbody = $('#walletTransactionTbody');
        tbody.html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: url,
            type: 'GET',

            success: function(response){

                if (response.statusCode == 200) {

                    $('#walletMemberId').val(memberId);
                    $('#walletMemberType').val(memberType);
                    $('#walletBalance').text('₹' + (response.data.walletBalance ?? 0.00));

                    // reset form fields
                    $('#amountInput').val('');
                    $('#amountErrorDiv').text('');
                    $('#walletRechargeForm')[0].reset();
                    $('#walletMemberId').val(memberId);
                    $('#walletMemberType').val(memberType);
                    $('#rechargeSubmitBtn').prop('disabled', false).html('Recharge Wallet').show();

                    tbody.empty();

                    let history = response.data.walletTransactionHistory;

                    if (history.length > 0) {

                        history.forEach(function(transaction) {

                            let amount = transaction.amount;
                            let direction = transaction.direction;

                            let amountClass = direction == 'credit' ? 'text-success' : 'text-danger';
                            let sign = direction == 'credit' ? '+' : '-';
                            let label = direction == 'credit' ? 'Added By' : 'Used';

                            let maker = transaction.creator?.name ?? '-';
                            let remarks = transaction.payment?.remarks ?? '';

                            let row = `
                                <tr>
                                    <td class="border-secondary bg-transparent align-middle lh-sm">
                                        <small class="fw-semibold">${label}:</small>
                                        <small class="text-black-50">${maker}</small>
                                        ${remarks ? `<br><small class="fw-semibold">Remarks:</small>
                                        <small class="text-black-50">${remarks}</small>` : ''}
                                    </td>
                                    <td class="${amountClass} text-end border-secondary bg-transparent align-middle">
                                        ${sign}₹${amount}
                                    </td>
                                </tr>
                            `;

                            tbody.append(row);
                        });

                    } else {
                        tbody.append('<tr><td colspan="2" class="text-center">No transactions found</td></tr>');
                    }

                    document.activeElement.blur();

                    if (keepCardEntry) {
                        // Open wallet modal on top of card punch popup
                        $('#walletrecharge').modal('show');
                    } else {
                        // Close card modal then open wallet modal
                        $('#cardentry').modal('hide');
                        setTimeout(() => {
                            $('#walletrecharge').modal('show');
                        }, 300);
                    }

                } else {
                    toastr.error('Failed to load wallet data');
                }
            },

            error: function(){
                toastr.error('Something Went Wrong.');
            }
        });
    }

    $('#walletRechargeForm').on('submit', function (e) {
        e.preventDefault();
        $('#confirmRechargeModal').modal('show');
    });

    /* Global confirmRechargeBtn handler — works for both club & swimming, from any page/modal */
    $(document).on('click', '#confirmRechargeBtn', function () {

        $('#confirmRechargeModal').modal('hide');

        let memberType = $('#walletMemberType').val();

        let rechargeUrl = '';
        if (memberType === 'swimming') {
            rechargeUrl = '{{ route("swimming-member.recharge-wallet-balance") }}';
        } else {
            rechargeUrl = '{{ route("club-member.recharge-wallet-balance") }}';
        }

        $('#rechargeSubmitBtn')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');

        let formData = new FormData($('#walletRechargeForm')[0]);

        $.ajax({
            url: rechargeUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(response) {

                if (response.statusCode == 200) {

                    toastr.success(response.message);

                    // If opened from card punch popup, update balance & close modal (no redirect)
                    if ($('#cardentry').hasClass('show')) {
                        let newBal = response.data ?? null;
                        if (newBal !== null) {
                            let balFormatted = parseFloat(newBal).toFixed(2);
                            $('#cardMemberWallet').text('Rs.' + balFormatted);
                            $('#walletBalance').text('₹' + balFormatted);
                        }
                        $('#rechargeSubmitBtn').prop('disabled', false).html('Recharge Wallet');
                        $('#walletrecharge').modal('hide');
                    } else {
                        // Opened from member list page — reload as before
                        $('#rechargeSubmitBtn').hide();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }

                } else {

                    toastr.error(response.message ?? 'Something went wrong');
                    $('#rechargeSubmitBtn').prop('disabled', false).html('Recharge Wallet');
                }
            },

            error: function() {
                toastr.error('Something went wrong, Please try again.');
                $('#rechargeSubmitBtn').prop('disabled', false).html('Recharge Wallet');
            }
        });
    });

    $('#notification').on('click', function(e){
        e.preventDefault();
        $.ajax({
            url: '{{route("readAllNotification")}}',
            type: 'GET',
            success: function(response){
                if (response.statusCode == 200) {
                    console.log('Notification Read');

                    // $('.spinner-border').replaceWith(originalBtn);
                    // $('#viewprofile').modal('show');
                }
                else{
                    // toastr.error('Something Went Wrong').
                    console.log(response);
                }

            },
            error: function(){
                toastr.error('Something Went Wrong.');
            }
        });
    });

    /* ===================== Global Membership History Modal JS ===================== */
    function openMembershipHistoryModal(memberId) {
        $('#membershipPlanTbody').html('<tr><td colspan="6" class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></td></tr>');
        $('#membershipplan').modal('show');

        $.ajax({
            url: '{{route("club-member.membership-plan", ":id")}}'.replace(':id', memberId),
            type: 'GET',
            success: function(response) {
                if (response.statusCode == 200) {
                    let tbody = $('#membershipPlanTbody');
                    tbody.empty();
                    response.data.forEach(function(plan) {
                        let fromDate = new Date(plan.start_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
                        let toDate   = new Date(plan.expiry_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
                        let isActive = new Date(plan.expiry_date) >= new Date() && plan.status !== 'cancelled';
                        tbody.append(`<tr>
                            <td class="bg-info align-middle p-3 text-nowrap">
                                <small class="fw-semibold">From Date</small><br>
                                <small class="text-black-50">${fromDate}</small>
                            </td>
                            <td class="bg-info align-middle p-3 text-nowrap">
                                <small class="fw-semibold">To Date</small><br>
                                <small class="text-black-50">${toDate}</small>
                            </td>
                            <td class="bg-info align-middle p-3 text-nowrap">
                                <small class="fw-semibold">Plan Type</small><br>
                                <small class="text-black-50">${plan.membership_plan_type.name}</small>
                            </td>
                            <td class="bg-info align-middle p-3 text-nowrap">
                                <small class="fw-semibold">Fine</small><br>
                                <small class="text-black-50">Rs ${plan.fine_amount}</small>
                            </td>
                            <td class="bg-info align-middle p-3 text-nowrap">
                                <small class="fw-semibold">Price</small><br>
                                <small class="text-black-50">Rs ${plan.net_amount}</small>
                            </td>
                            <td class="bg-info align-middle text-end p-3">
                                <img src="{{ asset('assets/images') }}${isActive ? '/active-tag.svg' : '/expire-tag.svg'}" alt="" width="${isActive ? 76 : 73}" height="${isActive ? 67 : 24}">
                            </td>
                        </tr>`);
                    });
                } else {
                    $('#membershipPlanTbody').html('<tr><td colspan="6" class="text-center text-muted py-3">No data found.</td></tr>');
                }
            },
            error: function() {
                toastr.error('Something Went Wrong.');
            }
        });
    }

    $('#membershipHistoryBtn').on('click', function() {
        var memberId = $('#cardentry').data('member-id');
        if (!memberId) { toastr.error('Member data missing'); return; }
        $('#cardentry').modal('hide');
        $('#cardentry').one('hidden.bs.modal', function() {
            openMembershipHistoryModal(memberId);
        });
    });

    $('#membershipplan').on('hidden.bs.modal', function() {
        if ($('#cardentry').data('member-id')) {
            $('#cardentry').modal('show');
        }
    });

    $(document).on('click', '.membershipPlanBtn', function() {
        openMembershipHistoryModal($(this).data('id'));
    });

    /* ===================== Global Renewal Modal JS ===================== */
    function openRenewalModal(memberId) {
        $('#renewalForm')[0].reset();
        $('#renewal_member_id').val(memberId);
        // $('#renewal_gst_pct').val('{{ $globalGstPercentage ?? 0 }}');
        $('#renewal_gst_pct').val('{{ $globalPlanPurchaseGstPercentage ?? 0 }}');
        $('#renewalFineAlert').hide();
        $('#renewalFineList').html('');
        $('#renewalTotalFine').text('₹0.00');
        $('#renewal_fine_amt').val('0');
        $('#renewal_receipt_amt').text('₹0.00');
        $('#renewal_gst_amt').val('');

        $.ajax({
            url: '{{route("club-member.view", ":id")}}'.replace(':id', memberId),
            type: 'GET',
            success: function(response) {
                if (response.statusCode == 200) {
                    const d = response.data;
                    const purchases = (d.purchase_history || []).slice().sort((a, b) =>
                        new Date(b.expiry_date) - new Date(a.expiry_date)
                    );
                    const purchase = purchases[0];

                    $('#renewal_member_name').text(d.name || '—');
                    $('#renewal_card_no').text(d.card_details?.card_no || '—');
                    $('#renewal_current_plan').text(purchase?.membership_plan_type?.name ?? '—');
                    $('#renewal_expiry_date').text(
                        purchase?.expiry_date ? new Date(purchase.expiry_date).toLocaleDateString('en-IN') : '—'
                    );

                    let fineTotal = 0, fineHtml = '';

                    const sf = response.suggested_fine;
                    if (sf && sf.has_fine) {
                        fineTotal += parseFloat(sf.amount);
                        fineHtml += `<div class="d-flex justify-content-between py-1 border-bottom border-danger border-opacity-25">
                            <span>Membership Expiry Fine (${sf.days} days × ₹${sf.per_day}/day)</span>
                            <span class="fw-semibold">₹${parseFloat(sf.amount).toFixed(2)}</span>
                        </div>`;
                    }

                    (response.fy_shortfalls || []).forEach(function(fs) {
                        fineTotal += parseFloat(fs.shortfall);
                        fineHtml += `<div class="d-flex justify-content-between py-1 border-bottom border-danger border-opacity-25">
                            <span>Min. Spend Shortfall FY ${fs.fy_label}
                                <small class="text-muted">(Spent ₹${parseFloat(fs.total_spend).toFixed(2)} of ₹${parseFloat(fs.minimum_required).toFixed(2)})</small>
                            </span>
                            <span class="fw-semibold">₹${parseFloat(fs.shortfall).toFixed(2)}</span>
                        </div>`;
                    });

                    (d.pending_fines || []).forEach(function(f) {
                        fineTotal += parseFloat(f.fine_amount);
                        let label;
                        if (f.fine_type === 'membership_expiry_fine') {
                            if (f.reference_days && f.reference_days > 0) {
                                const perDay = (parseFloat(f.fine_amount) / f.reference_days).toFixed(4);
                                label = `Membership Expiry Fine (${f.reference_days} days × ₹${perDay}/day)`;
                            } else {
                                label = 'Membership Expiry Fine';
                            }
                        } else {
                            label = 'Min. Spend Shortfall' + (f.financial_year ? ' FY ' + f.financial_year.fy_label : '');
                        }
                        fineHtml += `<div class="d-flex justify-content-between py-1 border-bottom border-danger border-opacity-25">
                            <span>${label}</span>
                            <span class="fw-semibold">₹${parseFloat(f.fine_amount).toFixed(2)}</span>
                        </div>`;
                    });

                    if (fineTotal > 0) {
                        $('#renewalFineList').html(fineHtml);
                        $('#renewalTotalFine').text('₹' + fineTotal.toFixed(2));
                        $('#renewal_fine_amt').val(fineTotal.toFixed(2));
                        $('#renewalFineAlert').show();
                    }

                    calcRenewalReceipt();
                }
                $('#planrenewal').modal('show');
            },
            error: function() {
                $('#planrenewal').modal('show');
            }
        });
    }

    function calcRenewalReceipt() {
        const taxable = parseFloat($('#renewal_taxable').val()) || 0;
        const gstPct  = parseFloat($('#renewal_gst_pct').val()) || 0;
        const fine    = parseFloat($('#renewal_fine_amt').val()) || 0;
        const gstAmt  = (taxable * gstPct) / 100;
        $('#renewal_gst_amt').val(gstAmt.toFixed(2));
        $('#renewal_receipt_amt').text('₹' + (taxable + gstAmt + fine).toFixed(2));
    }

    $('#renewal_taxable, #renewal_gst_pct, #renewal_fine_amt').on('input', calcRenewalReceipt);

    $(document).on('change', '.renewal-plan-type', function() {
        const price = parseFloat($(this).data('price')) || 0;
        $('#renewal_taxable').val(price > 0 ? price : '');
        calcRenewalReceipt();
    });

    $('#renewalForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#renewalSubmitBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');
        $.ajax({
            url: '{{ route("club-member.renew") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#cardentry').removeData('member-id');
                    $('#planrenewal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(response.message || 'Something went wrong');
                }
            },
            error: function() { toastr.error('Something went wrong'); },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-rotate-right me-1"></i> Submit Renewal');
            }
        });
    });

    /* ---- Renewal button in card punch modal ---- */
    $('#renewalBtn').on('click', function () {
        var memberId = $('#cardentry').data('member-id');
        if (!memberId) { toastr.error('Member data missing'); return; }
        $('#cardentry').modal('hide');
        $('#cardentry').one('hidden.bs.modal', function() {
            openRenewalModal(memberId);
        });
    });

    /* ---- Renewal button in member list table ---- */
    $(document).on('click', '.planRenewalBtn', function () {
        openRenewalModal($(this).data('id'));
    });

    /* Re-show #cardentry when renewal/swim-renewal modal is cancelled (not submitted) */
    var _renewalFromCard = false;
    $('#planrenewal').on('show.bs.modal', function() {
        _renewalFromCard = $('#cardentry').data('member-id') ? true : false;
    });
    $('#planrenewal').on('hidden.bs.modal', function() {
        if (_renewalFromCard && $('#cardentry').data('member-id')) {
            _renewalFromCard = false;
            $('#cardentry').modal('show');
        }
    });

    var _swimRenewalFromCard = false;
    $('#swimRenewalModal').on('show.bs.modal', function() {
        _swimRenewalFromCard = $('#cardentry').data('member-id') ? true : false;
    });
    $('#swimRenewalModal').on('hidden.bs.modal', function() {
        if (_swimRenewalFromCard && $('#cardentry').data('member-id')) {
            _swimRenewalFromCard = false;
            $('#cardentry').modal('show');
        }
    });

    /* ===================== Swimming Member Renewal Modal JS ===================== */
    function openSwimRenewalModal(memberId) {
        $('#swimRenewalForm')[0].reset();
        $('#swim_renewal_member_id').val(memberId);
        // $('#swim_renewal_gst_pct').val('{{ $globalGstPercentage ?? 0 }}');
        $('#swim_renewal_gst_pct').val('{{ $globalPlanPurchaseGstPercentage ?? 0 }}');
        $('#swimRenewalFineAlert').hide();
        $('#swimRenewalFineList').html('');
        $('#swimRenewalTotalFine').text('₹0.00');
        $('#swim_renewal_fine_amt').val('0');
        $('#swim_renewal_receipt_amt').text('₹0.00');
        $('#swim_renewal_gst_amt').val('');

        $.ajax({
            url: '{{route("swimming-member.view", ":id")}}'.replace(':id', memberId),
            type: 'GET',
            success: function(response) {
                if (response.statusCode == 200) {
                    const d = response.data;
                    const purchases = (d.purchase_history || []).slice().sort((a, b) =>
                        new Date(b.expiry_date) - new Date(a.expiry_date)
                    );
                    const purchase = purchases[0];

                    $('#swim_renewal_member_name').text(d.name || '—');
                    // $('#swim_renewal_card_no').text(d.card_details?.card_no || '—');
                    $('#swim_renewal_current_plan').text(purchase?.membership_plan_type?.name ?? '—');
                    $('#swim_renewal_expiry_date').text(
                        purchase?.expiry_date ? new Date(purchase.expiry_date).toLocaleDateString('en-IN') : '—'
                    );

                    let fineTotal = 0, fineHtml = '';

                    const sf = response.suggested_fine;
                    if (sf && sf.has_fine) {
                        fineTotal += parseFloat(sf.amount);
                        fineHtml += `<div class="d-flex justify-content-between py-1 border-bottom border-danger border-opacity-25">
                            <span>Membership Expiry Fine (${sf.days} days × ₹${sf.per_day}/day)</span>
                            <span class="fw-semibold">₹${parseFloat(sf.amount).toFixed(2)}</span>
                        </div>`;
                    }

                    (d.pending_fines || []).forEach(function(f) {
                        if (f.fine_type !== 'membership_expiry_fine') return;
                        fineTotal += parseFloat(f.fine_amount);
                        let label;
                        if (f.reference_days && f.reference_days > 0) {
                            const perDay = (parseFloat(f.fine_amount) / f.reference_days).toFixed(4);
                            label = `Membership Expiry Fine (${f.reference_days} days × ₹${perDay}/day)`;
                        } else {
                            label = 'Membership Expiry Fine';
                        }
                        fineHtml += `<div class="d-flex justify-content-between py-1 border-bottom border-danger border-opacity-25">
                            <span>${label}</span>
                            <span class="fw-semibold">₹${parseFloat(f.fine_amount).toFixed(2)}</span>
                        </div>`;
                    });

                    if (fineTotal > 0) {
                        $('#swimRenewalFineList').html(fineHtml);
                        $('#swimRenewalTotalFine').text('₹' + fineTotal.toFixed(2));
                        $('#swim_renewal_fine_amt').val(fineTotal.toFixed(2));
                        $('#swimRenewalFineAlert').show();
                    }

                    calcSwimRenewalReceipt();
                }
                $('#swimRenewalModal').modal('show');
            },
            error: function() {
                $('#swimRenewalModal').modal('show');
            }
        });
    }

    function calcSwimRenewalReceipt() {
        const taxable = parseFloat($('#swim_renewal_taxable').val()) || 0;
        const gstPct  = parseFloat($('#swim_renewal_gst_pct').val()) || 0;
        const fine    = parseFloat($('#swim_renewal_fine_amt').val()) || 0;
        const gstAmt  = (taxable * gstPct) / 100;
        $('#swim_renewal_gst_amt').val(gstAmt.toFixed(2));
        $('#swim_renewal_receipt_amt').text('₹' + (taxable + gstAmt + fine).toFixed(2));
    }

    $('#swim_renewal_taxable, #swim_renewal_gst_pct, #swim_renewal_fine_amt').on('input', calcSwimRenewalReceipt);

    $(document).on('change', '.swim-renewal-plan-type', function() {
        const price = parseFloat($(this).data('price')) || 0;
        $('#swim_renewal_taxable').val(price > 0 ? price : '');
        calcSwimRenewalReceipt();
    });

    $('#swimRenewalForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#swimRenewalSubmitBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');
        $.ajax({
            url: '{{ route("swimming-member.renew") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#cardentry').removeData('member-id');
                    $('#swimRenewalModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(response.message || 'Something went wrong');
                }
            },
            error: function() { toastr.error('Something went wrong'); },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-rotate-right me-1"></i> Submit Renewal');
            }
        });
    });

    /* ---- Swim Renewal button in card punch modal ---- */
    $('#swimRenewalBtn').on('click', function () {
        var memberId = $('#cardentry').data('member-id');
        if (!memberId) { toastr.error('Member data missing'); return; }
        $('#cardentry').modal('hide');
        $('#cardentry').one('hidden.bs.modal', function() {
            openSwimRenewalModal(memberId);
        });
    });

    /* ---- Swim Renewal button in member list table ---- */
    $(document).on('click', '.swimPlanRenewalBtn', function () {
        openSwimRenewalModal($(this).data('id'));
    });
</script>
