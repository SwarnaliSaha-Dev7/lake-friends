<!DOCTYPE html>
<html lang="en">

<head>
    @include('base.head')
</head>

<body>
    @yield('content')

    @yield('modalComponent')
    @include('base.scripts')
    @yield('customJS')
</body>

</html>
