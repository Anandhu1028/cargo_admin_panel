<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="dark" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Dashboard | Approx - Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

   
</head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    @include('admin.layouts.header')
    @include('admin.layouts.style')
</head>
<body>
    <div class="d-flex">
        @include('admin.layouts.sidebar')

        <div class="main-content flex-grow-1 p-4">
            @yield('content')
        </div>
    </div>
    @include('admin.layouts.footer')
    @include('admin.layouts.script')

    @stack('scripts')
</body>
</html>
