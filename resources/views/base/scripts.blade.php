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
                            toastr.error(response.error ?? 'Something Went Wrong.').
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
