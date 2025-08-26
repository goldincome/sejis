<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <title>@yield('title', 'User Panel') - Sejis</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="msapplication-TileImage" content="{{ asset('favicon.png') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <meta name="keywords" content="@yield('keywords')">

    <meta name="author" content="Sejis UK">
    <meta property="og:title" content="@yield('title')" />
    <meta property="og:description" content="@yield('description')" />
    <meta name="robots" content= "index, follow">
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ request()->url() }}" />
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;700;900&display=swap"
        rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'roboto', sans-serif;
            color: theme('colors.brand-text-dark');
        }

        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/3184405/pexels-photo-3184405.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 6rem 0;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
    @yield('css')
</head>

<body class="bg-gray-100">
    @include('front.common.header')
    <main>
        <section class="page-hero-section text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 font-pacifico">@yield('page_title')</h1>
                <p class="text-lg sm:text-xl max-w-2xl mx-auto text-brand-light-blue">@yield('page_intro')</p>
            </div>
        </section>
        <section class="py-16 lg:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    @include('front.common.sidebar')
                    @yield('content')

                </div>
            </div>
        </section>
    </main>
    @include('front.common.footer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });
            document.querySelectorAll('.section-animate').forEach(section => {
                observer.observe(section);
            });
        });
    </script>
    @yield('js')
</body>

</html>
