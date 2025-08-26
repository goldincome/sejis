
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <title>@yield('title') - VMC</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="msapplication-TileImage" content="{{ asset('favicon.png') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <meta name="author" content="Sejis">
    <meta property="og:title" content="@yield('title')"/>
    <meta property="og:description" content="@yield('description')"/>
    <meta name="robots" content= "index, follow">
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ request()->url() }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('og')

    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.png') }}">

    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{--  <link href="{{ asset('css/app.css') }}" rel="stylesheet">--}}
    @yield('css')

</head>


<body>
    @yield('content')
    @yield('js')
</body>
</html>
