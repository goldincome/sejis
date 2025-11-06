@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Contact Sejis Rentals - Get in Touch
@endsection

@section('keywords')
    contact sejis rentals, kitchen equipment rental support, book equipment, rental inquiries
@endsection
@section('description')
    Have a question? Need a quote? Contact the Sejis Rentals team via phone, email, or our contact form. We're ready to
    help equip your kitchen.
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

        /* Hero Image for Contact Page */
        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/3769138/pexels-photo-3769138.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
@endsection

@section('content')
    <main>
        <section class="page-hero-section text-white py-24 md:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Get In Touch</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">We're here to help answer your
                    questions and get you the equipment you need.
                </p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Contact
                    Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto text-center">
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <i class="fas fa-map-marker-alt text-accent text-5xl mb-6"></i>
                        <h3 class="text-2xl font-bold text-brand-deep-ash mb-2">Our Warehouse</h3>
                        <p class="text-lg text-gray-600">123 Culinary Way<br>Foodie City, ST 54321</p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <i class="fas fa-phone-alt text-accent text-5xl mb-6"></i>
                        <h3 class="text-2xl font-bold text-brand-deep-ash mb-2">Call or Email</h3>
                        <p class="text-lg text-gray-600">
                            <a href="tel:+1234567890" class="hover:text-accent">(123) 456-7890</a>
                            <br>
                            <a href="mailto:info@sejisrentals.com"
                                class="hover:text-accent">info@sejisrentals.com</a>
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                        <i class="fas fa-clock text-accent text-5xl mb-6"></i>
                        <h3 class="text-2xl font-bold text-brand-deep-ash mb-2">Business Hours</h3>
                        <p class="text-lg text-gray-600">Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 1:00 PM</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-6">Send Us a Message</h2>
                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                                role="alert">
                                <strong class="font-bold">Success!</strong>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6"
                                role="alert">
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('process-contact') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="name" name="name" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email
                                    Address</label>
                                <input type="email" id="email" name="email" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                            </div>
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" id="subject" name="subject" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea id="message" name="message" rows="6" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm"></textarea>
                            </div>
                            <div>
                                <button type="submit"
                                    class="w-full bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg transform hover:scale-105">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>

                    <div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-6">Find Us Here</h2>
                        <div class="w-full h-96 rounded-lg shadow-lg overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.088126017838!2d-122.4194154!3d37.7749295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80858064f9b8081f%3A0x8545f442b5818c7!2sSan%20Francisco%2C%20CA!5e0!3m2!1sen!2sus!4v1678888888888!5m2!1sen!2sus"
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
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