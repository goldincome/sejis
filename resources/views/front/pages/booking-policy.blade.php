@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Booking & Cancellation Policy - Sejis Rentals
@endsection

@section('keywords')
    booking policy, cancellation policy, rental refunds, equipment booking terms
@endsection
@section('description')
    Read the official booking, payment, and cancellation policy for Sejis Rentals. Understand our terms for deposits,
    refunds, and rental changes.
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
            @apply text-brand-deep-ash font-pacifico mb-4 mt-12;
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
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Booking & Cancellation
                    Policy</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Last Updated:
                    {{ now()->format('F j, Y') }}</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="prose prose-lg max-w-4xl mx-auto">
                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">1. Booking & Payment</h2>
                    <h3>Reservation</h3>
                    <p>
                        All bookings made through our website are considered requests until confirmed by a Sejis Rentals
                        team member. We reserve the right to refuse a booking for any reason.
                    </p>
                    <h3>Payment</h3>
                    <p>
                        Payment is due in full at the time of booking to secure your equipment. For long-term or
                        high-value rentals, a deposit may be required, with the balance due before pickup/delivery. This
                        will be specified in your rental quote.
                    </p>
                    <h3>Security Deposit</h3>
                    <p>
                        A refundable security deposit may be required for certain equipment. This deposit covers potential
                        damage, loss, or late returns. The deposit will be refunded within 5-7 business days after the
                        equipment is returned and inspected, provided it is in its original condition.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">2. Cancellation Policy</h2>
                    <h3>Cancellations by You</h3>
                    <p>We understand that plans change. Our cancellation policy is as follows:</p>
                    <ul>
                        <li><strong>More than 7 days before rental start date:</strong> Full refund of your rental fee,
                            minus any non-refundable processing fees.
                        </li>
                        <li><strong>Between 3 and 7 days before rental start date:</strong> 50% refund of your rental fee.
                        </li>
                        <li><strong>Less than 72 hours (3 days) before rental start date:</strong> No refund will be
                            issued.
                        </li>
                    </ul>
                    <p>All cancellation requests must be submitted in writing via email to [cancel@sejisrentals.com].</p>

                    <h3>Cancellations by Us</h3>
                    <p>
                        We reserve the right to cancel any booking at any time. If we cancel your booking for reasons other
                        than a breach of your rental agreement (e.g., equipment unavailability), you will receive a 100%
                        full refund.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">3. Rental Period</h2>
                    <h3>Pickup & Return</h3>
                    <p>
                        Your rental period begins and ends at the times specified in your booking confirmation. Equipment
                        returned late will be subject to additional daily rental fees at our standard rate, plus a late
                        fee.
                    </p>
                    <h3>Early Returns</h3>
                    <p>
                        No refunds or credits will be issued for equipment returned before the end of the specified rental
                        period.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">4. Equipment Condition</h2>
                    <p>
                        You are responsible for inspecting all equipment upon pickup/delivery. You must notify Sejis
                        Rentals immediately of any defects or damage. You are responsible for returning the equipment in the
                        same clean and working condition it was received. A cleaning fee may be assessed if equipment is
                        returned excessively dirty.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">5. Contact Us</h2>
                    <p>If you have any questions about this Booking Policy, please <a
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