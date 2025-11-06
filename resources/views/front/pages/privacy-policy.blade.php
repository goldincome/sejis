@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Privacy Policy - Sejis Rentals
@endsection

@section('keywords')
    privacy policy, user data, data protection, sejis rentals privacy
@endsection
@section('description')
    Understand how Sejis Rentals collects, uses, and protects your personal data when you use our website and rental
    services.
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
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Privacy Policy</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Last Updated:
                    {{ now()->format('F j, Y') }}</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="prose prose-lg max-w-4xl mx-auto">
                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">1. Introduction</h2>
                    <p>Sejis Rentals ("us", "we", or "our") operates the [Your Website URL] website (the "Service"). This
                        page informs you of our policies regarding the collection, use, and disclosure of personal data when
                        you use our Service and the choices you have associated with that data.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">2. Information Collection and Use</h2>
                    <p>We collect several different types of information for various purposes to provide and improve our
                        Service to you.
                    </p>
                    <h3>Types of Data Collected</h3>
                    <ul>
                        <li><strong>Personal Data:</strong> While using our Service, we may ask you to provide us with
                            certain personally identifiable information, such as your name, email address, phone number,
                            and billing address, to process your rental bookings.
                        </li>
                        <li><strong>Usage Data:</strong> We may also collect information on how the Service is accessed and
                            used (e.g., IP address, browser type, pages visited).
                        </li>
                        <li><strong>Cookies Data:</strong> We use cookies and similar tracking technologies to track
                            activity on our Service.
                        </li>
                    </ul>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">3. Use of Data</h2>
                    <p>Sejis Rentals uses the collected data for various purposes:</p>
                    <ul>
                        <li>To provide and maintain our Service (e.g., process your bookings).</li>
                        <li>To notify you about changes to our Service.</li>
                        <li>To provide customer support.</li>
                        <li>To gather analysis or valuable information so that we can improve our Service.</li>
                        <li>To detect, prevent, and address technical issues.</li>
                    </ul>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">4. Data Security</h2>
                    <p>The security of your data is important to us. We use commercially acceptable means to protect your
                        Personal Data, but remember that no method of transmission over the Internet or method of electronic
                        storage is 100% secure.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">5. Service Providers</h2>
                    <p>We may employ third-party companies (e.g., payment processors) to facilitate our Service. These
                        third parties have access to your Personal Data only to perform these tasks on our behalf and are
                        obligated not to disclose or use it for any other purpose.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">6. Your Data Protection Rights</h2>
                    <p>You have certain data protection rights. You are entitled to access, update, or delete the
                        information we have on you. If you wish to be informed about what Personal Data we hold about you or
                        if you want it to be removed from our systems, please contact us.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">7. Changes to This Privacy Policy</h2>
                    <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the
                        new Privacy Policy on this page.
                    </p>

                    <h2 class="text-3xl sm:text-3xl font-bold  text-brand-deep-ash mb-6 mt-3 font-pacifico">8. Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please <a
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