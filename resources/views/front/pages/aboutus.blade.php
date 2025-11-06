@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    About Sejis Rentals - Our Story & Mission
@endsection

@section('keywords')
    about sejis rentals, kitchen equipment rental company, our mission, professional kitchen solutions
@endsection
@section('description')
    Learn about Sejis Rentals, our mission to support culinary professionals, and our commitment to quality, flexibility, and
    service.
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

        /* Hero Image for About Us Page */
        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .team-member-img {
            height: 250px;
            object-fit: cover;
            object-position: top;
        }
    </style>
@endsection

@section('content')
    <main>
        <section class="page-hero-section text-white py-24 md:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">About Sejis Rentals</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Powering culinary success, one rental
                    at a time. We're your dedicated partner for professional kitchen equipment.</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-6 font-pacifico">Our Story</h2>
                        <div class="space-y-4 text-lg text-gray-700">
                            <p>Founded in [Year] by a team of food industry veterans, Sejis Rentals was born from a simple
                                observation: culinary creativity shouldn't be limited by equipment access. We saw talented
                                caterers, ambitious pop-up chefs, and growing food businesses struggling with the immense
                                cost and logistical headaches of owning specialized kitchen gear.</p>
                            <p>We built Sejis Rentals to be the solution. We're not just a rental company; we're an
                                extension of your kitchen. We provide the high-quality, reliable, and clean equipment you
                                need, exactly when you need it, so you can focus on what you do best—creating unforgettable
                                food.</p>
                        </div>
                    </div>
                    <div>
                        <img src="https://images.pexels.com/photos/10687777/pexels-photo-10687777.jpeg?auto=compress&cs=tinysrgb&w=800"
                            alt="Professional kitchen setup" class="rounded-lg shadow-xl w-full h-auto object-cover">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-5xl section-animate">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-gray-50 p-8 rounded-lg shadow-lg text-center">
                        <i class="fas fa-bullseye text-accent text-5xl mb-6"></i>
                        <h3 class="text-3xl font-bold text-brand-deep-ash mb-4">Our Mission</h3>
                        <p class="text-lg text-gray-600">To empower culinary professionals by providing flexible,
                            cost-effective, and reliable access to professional-grade kitchen equipment, helping them grow
                            their businesses and achieve their creative vision.</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-lg shadow-lg text-center">
                        <i class="fas fa-eye text-accent text-5xl mb-6"></i>
                        <h3 class="text-3xl font-bold text-brand-deep-ash mb-4">Our Vision</h3>
                        <p class="text-lg text-gray-600">To be the most trusted and indispensable partner for food
                            businesses, known for our exceptional service, quality inventory, and unwavering support for
                            the culinary community.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-brand-light-blue">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Our
                    Commitment to You</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-award"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Pro-Grade Quality</h3>
                        <p class="text-gray-600">Access top-tier, industry-standard brands that deliver reliable
                            performance.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-piggy-bank"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Cost-Effective</h3>
                        <p class="text-gray-600">Avoid high purchase, maintenance, and storage costs. Pay only for what
                            you use.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-calendar-check"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Flexible Terms</h3>
                        <p class="text-gray-600">Rent for a day, a week, or a month. We offer terms that match your
                            project's needs.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-soap"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Clean & Maintained</h3>
                        <p class="text-gray-600">All equipment is professionally cleaned, sanitized, and tested before
                            every rental.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Meet Our
                    Team</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-gray-50 rounded-lg shadow-lg overflow-hidden text-center">
                        <img src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=600&h=600&fit=crop"
                            alt="Team Member 1" class="w-full team-member-img">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-brand-deep-ash mb-1">John Doe</h3>
                            <p class="text-accent font-semibold text-lg">Founder & CEO</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg shadow-lg overflow-hidden text-center">
                        <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=600&h=600&fit=crop"
                            alt="Team Member 2" class="w-full team-member-img">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-brand-deep-ash mb-1">Jane Smith</h3>
                            <p class="text-accent font-semibold text-lg">Operations Manager</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg shadow-lg overflow-hidden text-center">
                        <img src="https://images.pexels.com/photos/1043473/pexels-photo-1043473.jpeg?auto=compress&cs=tinysrgb&w=600&h=600&fit=crop"
                            alt="Team Member 3" class="w-full team-member-img">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-brand-deep-ash mb-1">Mike Johnson</h3>
                            <p class="text-accent font-semibold text-lg">Lead Technician</p>
                        </div>
                    </div>
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