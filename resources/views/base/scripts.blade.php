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


                            let memberId = response.data.id;
                            let memberType = response.data.member_details?.membership_type?.name?.toLowerCase();

                            if (!memberType) {
                                toastr.error('Membership type not found');
                                return;
                            }

                            if (memberType.includes('club')) {
                                memberType = 'club';
                            }
                            else if (memberType.includes('swimming')) {
                                memberType = 'swimming';
                            }

                            $('#cardentry').data('member-id', memberId);
                            $('#cardentry').data('member-type', memberType);

                            $('#cardMemberName').text(response.data.name);
                            $('#cardMemberClubName').text(response.data.club_details.name)
                            $('#cardMemberCode').text(response.data.member_code)
                            $('#cardMemberCardNo').text(response.data.card_details.card_no)
                            $('#cardMemberPlan').text(response.data.purchase_history[0].membership_plan_type.name)
                            let expiryDate = response.data.purchase_history[0].expiry_date;
                            let formatted = new Date(expiryDate).toLocaleDateString('en-IN'); // d/m/y format
                            $('#cardMemberPlanExpiry').text(formatted);
                            $('#cardMemberWallet').text('₹ ' + (response.data.wallet_details?.current_balance??0));
                            $('#cardStatus').text(response.cardStatus);
                            $('#memberStatus').text(humanStatus);

                            $('#cardentry').modal('show');

                            $('.swipe-animation').show();
                            $('#cardLoader').hide();
                            // $('.spinner-border').replaceWith(originalBtn);
                            // $('#viewprofile').modal('show');
                        }
                        else{
                            $('#cardLoader').hide();
                            $('.swipe-animation').show();
                            toastr.error(response.error ?? 'Something Went Wrong.');
                            //console.log(response);
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

        loadWalletData(url, memberId);
    });

    function loadWalletData(url, memberId) {

        let tbody = $('#walletTransactionTbody');

        tbody.html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: url,
            type: 'GET',

            success: function(response){

                if (response.statusCode == 200) {

                    $('#walletMemberId').val(memberId);
                    $('#walletBalance').text('₹' + (response.data.walletBalance ?? 0.00));

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

                                        ${remarks ? `<br>
                                        <small class="fw-semibold">Remarks:</small>
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

                    // Close card modal
                    $('#cardentry').modal('hide');

                    // Open wallet modal
                    setTimeout(() => {
                        $('#walletrecharge').modal('show');
                    }, 300);

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
</script>
