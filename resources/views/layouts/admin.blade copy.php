<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitchen Rental') }} - Admin</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#f97316',
                    }
                }
            }
        }
    </script>
    @stack('css')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @include('admin.common.sidebar')

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Navigation -->
            @include('admin.common.topnav')

            <!-- Page Content -->
            <main class="bg-gray-50 min-h-screen">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    @include('admin.common.error-and-message')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>