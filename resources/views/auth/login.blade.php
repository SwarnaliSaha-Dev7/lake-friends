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
                                <img src="{{ asset('assets/images/logo.svg') }}" alt="img" loading="lazy" fetchpriority="auto" width="97"
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
                                                <div class="form-part mb-3">
                                                    <input type="email" class="form-control py-2 shadow-none"
                                                        id="loginInputEmail1" name="email" placeholder="Email" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input type="password" class="form-control py-2 shadow-none"
                                                        id="loginInputPassword1" name="password" placeholder="Password" required>
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
                                                    <input type="email" class="form-control py-2 shadow-none"
                                                        id="loginInputEmail2" placeholder="Enter your email">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 mt-4 mb-1 fw-semibold py-2"
                                            data-bs-toggle="modal" data-bs-target="#otpModal">Send
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
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-part mb-3">
                                                    <input type="password" class="form-control py-2 shadow-none"
                                                        id="loginInputPassword4" placeholder="Confirm password"
                                                        >
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit"
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
                            <button type="submit" class="btn btn-primary mt-4 fw-semibold py-2">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
@endsection
