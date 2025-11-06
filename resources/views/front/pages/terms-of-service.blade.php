@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Terms of Service - Sejis Rentals
@endsection

@section('keywords')
    terms of service, legal, rental agreement, terms and conditions
@endsection
@section('description')
    Please read the Terms of Service for Sejis Rentals. This agreement governs your use of our website and rental services.
@endsection

@section('css')
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'roboto', sans-serif;
            color: theme('colors.brand-text-dark');
        }

        /* Generic Hero Image for Legal Pages */
        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/4831/calendar-coffee-current-desk.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        /* Styling for prose content */
        .prose h2 {
            @apply text-brand-deep-ash font-pacifico font-bold text-4xl mb-6 mt-16;
        }

        .prose h3 {
            @apply text-brand-deep-ash mb-2 mt-8;
        }
    </style>
@endsection

@section('content')
    <main>
        <section class="page-hero-section text-white py-24 md:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Terms of Service</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Last Updated:
                    {{ now()->format('F j, Y') }}</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="prose prose-lg max-w-4xl mx-auto">
                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">1. Agreement to Terms</h2>
                    <p>By accessing our website or using our equipment rental services ("Service"), you agree to be bound
                        by these Terms of Service ("Terms"). If you disagree with any part of the terms, you may not access
                        the Service.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">2. Rental Agreement</h2>
                    <p>All equipment rentals are subject to the Sejis Rentals Rental Agreement, which will be provided at
                        the time of booking. This includes, but is not limited to, terms regarding:</p>
                    <ul>
                        <li>Rental fees, deposits, and payment schedules.</li>
                        <li>Rental duration, including pickup and return times.</li>
                        <li>Cancellation and refund policies (see our Booking Policy).</li>
                        <li>Your responsibility for the equipment, including loss, damage, and proper use.</li>
                    </ul>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">3. Use of Our Website</h2>
                    <h3>User Accounts</h3>
                    <p>You may be required to create an account to access certain features. You are responsible for
                        safeguarding your password and for any activities or actions under your password.
                    </p>
                    <h3>Prohibited Uses</h3>
                    <p>You agree not to use the website:</p>
                    <ul>
                        <li>In any way that violates any applicable local, national, or international law.</li>
                        <li>To transmit any unsolicited or unauthorized advertising or promotional material.</li>
                        <li>To impersonate or attempt to impersonate the Company, a Company employee, or another user.
                        </li>
                    </ul>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">4. Intellectual Property</h2>
                    <p>The Service and its original content (excluding content provided by users), features, and
                        functionality are and will remain the exclusive property of Sejis Rentals and its licensors.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">5. Limitation of Liability</h2>
                    <p>In no event shall Sejis Rentals, nor its directors, employees, partners, or agents, be liable for
                        any indirect, incidental, special, consequential, or punitive damages, including without
                        limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i)
                        your access to or use of or inability to access or use the Service; (ii) any conduct or content of
                        any third party on the Service; and (iii) unauthorized access, use, or alteration of your
                        transmissions or content.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">6. Governing Law</h2>
                    <p>These Terms shall be governed and construed in accordance with the laws of [Your State/Country],
                        without regard to its conflict of law provisions.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">7. Changes to Terms</h2>
                    <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. We will
                        provide at least 30 days' notice prior to any new terms taking effect.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">8. Contact Us</h2>
                    <p>If you have any questions about these Terms, please <a
                            href="{{ route('contact-us') }}">contact us</a>.</p>
                </div>
            </div>
        </section>

    </main>
@endsection


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Scroll Animation ---
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
@endsection