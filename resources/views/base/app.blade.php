<!DOCTYPE html>
<html lang="en">

<head>
    @include('base.head')
</head>

<body>
    <section class="dashboard-wrapper">

        {{-- Sidebar --}}
        @include('partials.sidebar')
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
    @include('base.scripts')
    @yield('customJS')
</body>

</html>
