<div class="right-header d-flex align-items-center justify-content-between py-3 ps-lg-5 pe-3 ps-3 bg-white position-fixed top-0">
    <div class="item-left">
        <h1 class="mb-1 fs-3 fw-semibold">@yield('page_title')</h1>
        {{-- <p class="m-0">Overview of your member management system</p> --}}
    </div>
    <div class="item-right d-flex align-items-center position-relative">
        <div class="notification position-relative">
            <a href="#" class="position-relative" id="notification"><i class="fa-regular fa-bell"></i>
                <span
                    class="position-absolute translate-middle badge border border-light rounded-circle bg-danger p-1">@auth
@if(auth()->user()->unreadNotifications()->count() > 0)
{{ auth()->user()->unreadNotifications()->count() }}
@endif
@endauth</span>
            </a>
            <div
                class="notification-dropdown position-absolute top-100 end-0 rounded-3 text-white overflow-hidden">
                <div class="p-3 noti-head">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h2 class="fs-5 fw-semibold mb-0">Notification</h2>
                        <button type="button" class="close-notific border-0 bg-transparent fs-6 lh-1"><i
                                class="fa-regular fa-circle-xmark"></i></button>
                    </div>
                    <button class="btn btn-primary"><small class="fw-medium">All</small>
                        @auth
                            @if(auth()->user()->unreadNotifications()->count() > 0)
                                <span
                                    class="badge border border-danger rounded-circle bg-danger p-1 ms-2">
                                        {{ auth()->user()->unreadNotifications()->count() }}
                                </span>
                            @endif
                        @endauth
                    </button>
                </div>
                <div class="pt-3 pb-4 px-3 noti-body overflow-auto">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                    {{-- <a href="{{ route('notification.read', $notification->id) }}">
                        {{ $notification->data['message'] }} --}}
                         <div class="card text-white mb-2">
                            <div class="card-body">
                                <h5 class="card-title fs-6 fw-semibold"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"
                                        style="background: #F1F0FF1A;"><i class="fa-regular fa-credit-card"></i></span>{{ $notification->data['title'] ?? 'Notification' }}</h5>
                                <p class="card-text fw-medium"><small>{{ $notification->data['message'] ?? '' }}</small></p>
                                <p class="card-text fw-medium post-time"><small><i
                                            class="fa-regular fa-clock"></i> {{ $notification->created_at->diffForHumans() }}</small></p>
                            </div>
                        </div>
                    {{-- </a> --}}
                    @empty
                        <div class="text-center py-4">
                            <small>No new notifications</small>
                        </div>
                    @endforelse

                    {{-- <div class="card text-white mb-2">
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-semibold"><span
                                    class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"
                                    style="background: #F1F0FF1A;"><i class="fa-regular fa-credit-card"></i></span>Card Details Updated</h5>
                            <p class="card-text fw-medium"><small>Your Platinum membership card information
                                    has been successfully updated with new billing details.</small></p>
                            <p class="card-text fw-medium post-time"><small><i
                                        class="fa-regular fa-clock"></i> 5 min ago</small></p>
                        </div>
                    </div>
                    <div class="card text-white mb-2">
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-semibold"><span
                                    class="text-danger rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"
                                    style="background: #EA3F4159;"><i class="fa-solid fa-triangle-exclamation"></i></span>Card Expiring Soon</h5>
                            <p class="card-text fw-medium"><small>Your Platinum membership card information
                                    has been successfully updated with new billing details.</small></p>
                            <p class="card-text fw-medium post-time"><small><i
                                        class="fa-regular fa-clock"></i> 5 min ago</small></p>
                        </div>
                    </div>
                    <div class="card text-white mb-2">
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-semibold"><span
                                    class="text-danger rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"
                                    style="background: #EA3F4159;"><i class="fa-solid fa-triangle-exclamation"></i></span>Card Expiring Soon</h5>
                            <p class="card-text fw-medium"><small>Your Platinum membership card information
                                    has been successfully updated with new billing details.</small></p>
                            <p class="card-text fw-medium post-time"><small><i
                                        class="fa-regular fa-clock"></i> 5 min ago</small></p>
                        </div>
                    </div> --}}
                </div>
                {{-- <div class="notific-view-all position-absolute bottom-0 w-100">
                    <button class="btn border-0 fw-semibold lh-0 rounded-0 p-1 w-100 text-primary">View
                        all</button>
                </div> --}}
            </div>
        </div>
        <div class="login-user border-start ps-2 ms-2">
            <span class="auth-user fw-medium">{{ ucwords(Auth::user()->name) }}</span> <span
                class="d-inline-flex justify-content-center align-items-center rounded-3 text-white ms-2 log-user-icon"><i
                    class="fa-regular fa-user"></i></span>
        </div>
        <div class="login-dropdown p-3 rounded-3">
            <ul class="m-0 list-unstyled">
                <li><a href="javascript:void(0)"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
                {{-- <li><a href="login.html"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li> --}}
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent" style=" color: white; ">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
