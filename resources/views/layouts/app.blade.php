<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <title>@yield('title') - Sejis</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="msapplication-TileImage" content="{{ asset('favicon.png') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
         <meta name="keywords" content="@yield('keywords')">
    
        <meta name="author" content="Sejis UK">
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

            <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @yield('css')
    </head>
    <body class="bg-gray-50">
         @include('front.common.header')
        
        @yield('content')

        @include('front.common.footer')
        @yield('js')
        <script>
           document.addEventListener('DOMContentLoaded', function() { 
                // Mobile menu toggle
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenuButton) mobileMenuButton.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));


                // FAQ Accordion
                const faqQuestions = document.querySelectorAll('.faq-question');
                faqQuestions.forEach(question => {
                    question.addEventListener('click', () => {
                        const answer = question.nextElementSibling;
                        const iconSvg = question.querySelector('svg');
                        
                        faqQuestions.forEach(otherQuestion => {
                            if (otherQuestion !== question) {
                                otherQuestion.nextElementSibling.classList.add('hidden');
                                otherQuestion.classList.remove('open');
                                otherQuestion.querySelector('svg').classList.remove('rotate-180');
                            }
                        });

                        answer.classList.toggle('hidden');
                        question.classList.toggle('open');
                        iconSvg.classList.toggle('rotate-180');
                    });
                });

                // Set current year in footer
                const currentYearEl = document.getElementById('currentYear');
                if (currentYearEl) {
                    currentYearEl.textContent = new Date().getFullYear();
                }

                // --- FIX: Add Intersection Observer for animations ---
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, {
                    threshold: 0.1 // Trigger when 10% of the element is visible
                });

                const animatedElements = document.querySelectorAll('.section-animate');
                animatedElements.forEach(el => observer.observe(el));
               
                const heroButton = document.querySelector('.animate-bounce-custom');
                if (heroButton) {
                    // Custom animations can go here
                }
            });
        </script>
    </body>
</html>
