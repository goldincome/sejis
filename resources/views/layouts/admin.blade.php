<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitchen Rental') }} - Admin</title>

    <meta name="msapplication-TileImage" content="{{ asset('favicon.png') }}">
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
   <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'roboto', sans-serif; }
        .admin-sidebar a.active { 
            background-color: #0b0c0e; 
            color: #ffffff; 
        }
        .admin-sidebar a.active i { color: #f97316; }
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
    </style>
    
    @stack('css')
</head>
<body class="bg-gray-100">
     <div class="relative min-h-screen lg:flex">
        <!-- Sidebar -->
        @include('admin.common.sidebar')
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            @include('admin.common.topnav')

            <!-- Page Content -->
            @include('admin.common.error-and-message')
            @yield('content')
        </div>
        <!-- Overlay for mobile menu -->
        <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-20"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuButton = document.getElementById('menu-button');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            // Function to toggle the sidebar
            const toggleSidebar = () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
                overlay.classList.toggle('hidden');
            };

            // Event listener for the menu button
            if(menuButton) {
                menuButton.addEventListener('click', toggleSidebar);
            }

            // Event listener for the overlay to close the sidebar
            if(overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>