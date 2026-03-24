@extends('base.beforeLoginApp')

@section('title', 'Login')

@section('content')
    <div class="login-main-wrapper px-3 min-vh-100">
        <div class="login-holder d-flex align-items-center justify-content-center min-vh-100">
            <div class="container">
                <div class="login-light-box bg-white my-3 rounded-3 overflow-hidden">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="login-left w-100 h-100 rounded-3 overflow-hidden position-relative">
                                <img src="{{ asset($clubDetails->logo) }}" alt="img" loading="lazy" fetchpriority="auto" width="97"
                                    height="106" class="position-absolute overlay-logo">
                                <img src="{{ asset('assets/images/login-img.webp') }}" alt="img" loading="lazy" fetchpriority="auto"
                                    width="710" height="852" class="w-100 h-100 object-fit-cover">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="login-right">
                                <h2 class="mb-lg-0 mb-1"><strong>Lake Friend</strong></h2>
                                <h4 class="fw-medium">In services of sports</h4>

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <!-- step 1 start -->
                                    <div class="login-step1">
                                        <h4 class="fw-semibold text-dark mb-lg-4">Login</h4>
                                        <div class="row">
                                            <div class="col-12">
                                                @if ($errors->any())
                                                    {{-- <div class="text-danger">{{ $message }}</div> --}}
                                                    <div class="alert alert-danger">
                                                        {{ $errors->first() }}
                                                    </div>
                                                @endif
                                                <div class="form-part mb-3">
                                                    <input type="email" class="form-control py-2 shadow-none"
                                                        id="loginInputEmail1" name="email" placeholder="Email" required value="{{ old('email') }}">
                                                    {{-- <x-input-error :messages="$errors->get('email')" class="mt-2" /> --}}
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input type="password" class="form-control py-2 shadow-none"
                                                        id="loginInputPassword1" name="password" placeholder="Password" required>
                                                    {{-- <x-input-error :messages="$errors->get('password')" class="mt-2" /> --}}
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-end">
                                                    <a href=""><small>Forgot password ?</small></a>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="submit"
                                            class="btn btn-primary w-100 mt-5 mb-1 fw-semibold py-2" value="Submit">
                                    </div>
                                    <!-- step 1 end -->

                                    <!-- step 2 start -->
                                    <div class="login-step2 d-none">
                                        <h4 class="fw-semibold text-dark mb-lg-4">Forget Password</h4>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input class="form-control py-2 shadow-none"
                                                        id="loginInputEmail2" placeholder="Enter your email">
                                                        <span id="sendOtpEmailError" class="text-danger small"></span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <button type="submit" id="sendOtpBtn" class="btn btn-primary w-100 mt-4 mb-1 fw-semibold py-2">Send
                                            OTP</button> --}}
                                        <button type="button" id="sendOtpBtn" class="btn btn-primary w-100 mt-4 mb-1 fw-semibold py-2">Send
                                            OTP</button>
                                    </div>
                                    <!-- step 2 end -->

                                    <!-- step 3 start -->
                                    <div class="login-step3 d-none">
                                        <h4 class="fw-semibold text-dark mb-lg-4">Reset Password</h4>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input type="password" class="form-control py-2 shadow-none"
                                                        id="loginInputPassword3" placeholder="New password">
                                                    <span class="text-danger small error-text"></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input type="password" class="form-control py-2 shadow-none"
                                                        id="loginInputPassword4" placeholder="Confirm password"
                                                        >
                                                    <span class="text-danger small error-text"></span>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <button type="submit" --}}
                                        <button type="button" id="reset-password"
                                            class="btn btn-primary w-100 mt-4 mb-1 fw-semibold py-2">Reset
                                            password</button>
                                        <div class="text-center fw-semibold mt-3"><a href="#"><i
                                                    class="fa-solid fa-arrow-left me-3"></i>Back to login</a></div>
                                    </div>
                                    <!-- step 3 end -->
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h3 class="modal-title fw-semibold fs-5" id="otpModalLabel">Enter OTP</h3>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="form-part mb-3">
                            <input type="text" class="form-control py-2 shadow-none" id="loginInputotp"
                                placeholder="Enter your OTP" required>
                            <button type="submit" id="verifyOTP" class="btn btn-primary mt-4 fw-semibold py-2">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
    $(document).ready(function () {
        @if(session('inactive_error'))
            toastr.error("{{ session('inactive_error') }}");
        @endif

        $('#sendOtpBtn').click(function () {

            let btn = $(this); // button reference
            let email = $('#loginInputEmail2').val().trim();
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            $('#sendOtpEmailError').text(''); // clear old error

            if (email === '') {
                $('#sendOtpEmailError').text('Email is required');
                return;
            }

            if (!emailPattern.test(email)) {
                $('#sendOtpEmailError').text('Enter a valid email');
                return;
            }

            //disable button
            btn.prop('disabled', true).text('Sending...');

            $.ajax({
                url: "{{ route('sendOTP') }}",
                type: "POST",
                    data:{
                    "_token": "{{ csrf_token() }}",
                    "email": email
                    },
                success: function(response) {
                    // console.log(response);
                    if (response.statusCode == 200) {
                        toastr.success("OTP sent successfully.");
                        $('#otpModal').modal('show');

                        // setTimeout(function() {
                        //     location.reload();
                        // }, 1500);
                    } else {
                        // toastr.error("Failed to send OTP, Please try again.");
                        toastr.error(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    toastr.error("Failed to send OTP, Please try again.");
                    // console.error(xhr.responseText);
                },
                complete: function () {
                    //enable again (runs for success + error)
                    btn.prop('disabled', false).text('Send OTP');
                }
            });
        });

        // $('#verifyOTP').on('click', function (e) {
        //     e.preventDefault();
        //     let otp = $('#loginInputotp').val().trim();
        //     // let email = $('#loginInputEmail2').val().trim();

        //     if (otp === '') {
        //         toastr.error('Please enter the OTP.')
        //     }
        //     else{
        //         $('#otpModal form').submit();
        //     }
        // });

        $('#verifyOTP').on('click', function (e) {
            e.preventDefault();
            let otp = $('#loginInputotp').val().trim();
            let email = $('#loginInputEmail2').val().trim();

            if (otp === '') {
                toastr.error('Please enter the complete OTP.')
            }
            else{
                $.ajax({
                    url: "{{ route('verifyOTP') }}",
                    type: "POST",
                    data:{
                        "_token": "{{ csrf_token() }}",
                        "email": email,
                        "otp": otp
                        },
                    success: function(response) {
                        // console.log(response);
                        if (response.statusCode == 200) {
                            toastr.success("OTP verified successfully.");
                            $('#otpModal form').submit();
                        }
                        else if ((response.statusCode == 500) && response.message){
                            toastr.error(response.message);
                        }
                        else {
                            toastr.error("Something went wrong, Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        console.error(xhr.responseText);
                    }
                });
            }
        });

        $('#reset-password').on('click', function (e) {
            e.preventDefault();
            let btn = $(this);   // button reference
            btn.prop('disabled', true); // disable button
            // remove old errors
            $('.error-text').text('');
            // let otp = $('#loginInputotp').val().trim();
            let email = $('#loginInputEmail2').val().trim();
            let newPassword = $('#loginInputPassword3').val();
            let confirmPassword = $('#loginInputPassword4').val();

            let isValid = true;

            // New Password
            if (newPassword === '') {
                $('#loginInputPassword3').next('.error-text').text('Enter new password');
                isValid = false;
            } else if (newPassword.length < 6) {
                $('#loginInputPassword3').next('.error-text').text('Minimum 8 characters required');
                isValid = false;
            }

            // Confirm Password
            if (confirmPassword === '') {
                $('#loginInputPassword4').next('.error-text').text('Confirm your password');
                isValid = false;
            } else if (newPassword !== confirmPassword) {
                $('#loginInputPassword4').next('.error-text').text('Passwords do not match');
                isValid = false;
            }

            // if (otp === '') {
            //     toastr.error('Please enter the OTP.')
            //     $('#otpModal').modal('show');
            //     isValid = false;
            // }

            if (!isValid) {
                btn.prop('disabled', false);
                return;
            }

            $.ajax({
                url: "{{ route('resetNewPassword') }}",
                type: "POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "email": email,
                    // "otp": otp,
                    "newPassword": newPassword,
                    "confirmPassword": confirmPassword
                },
                success: function(response) {
                    // console.log(response);
                    if (response.statusCode == 200) {
                        toastr.success("Password reset successfully. Please Login");
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    }
                    else {
                        toastr.error(response.message);
                    }

                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr.responseText);
                },
                complete: function () {
                    // always enable again (success or error)
                    btn.prop('disabled', false);
                }
            });

        });

    });
</script>
@endsection
