<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - ERP Dian Mustika</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/matrix-admin/assets/images/favicon.png') }}">
    <link href="{{ asset('theme/matrix-admin/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/matrix-admin/matrix-compat.css') }}" rel="stylesheet">
</head>
<body>
<div class="main-wrapper">
    <div class="preloader">
        <div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div>
    </div>
    <div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark">
        <div class="auth-box bg-dark border-top border-secondary">
            @yield('content')
        </div>
    </div>
</div>
<script src="{{ asset('theme/matrix-admin/assets/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('theme/matrix-admin/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('theme/matrix-admin/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script>
    $('[data-toggle="tooltip"]').tooltip();
    $('.preloader').fadeOut();
</script>
@stack('scripts')
</body>
</html>
