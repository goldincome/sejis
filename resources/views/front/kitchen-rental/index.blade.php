@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Book Commercial Kitchen - Sejis Rentals
@endsection

@section('keywords')
    rent commercial kitchen, book kitchen space, hourly kitchen rental, catering kitchen, ghost kitchen space, food
    production kitchen, licensed kitchen for rent, kitchen gallery, book kitchen online
@endsection
@section('description')
    Rent our state-of-the-art, fully-equipped commercial kitchens. Perfect for catering, food production, pop-ups, and more.
    View our gallery and book your space online today!
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

        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/887827/pexels-photo-887827.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .main-gallery-image {
            height: 450px;
            object-fit: cover;
        }

        .thumbnail {
            @apply w-24 h-20 object-cover rounded-md cursor-pointer border-2 border-transparent hover:border-accent transition;
        }

        .thumbnail.active {
            @apply border-accent ring-2 ring-accent/50;
        }

        .gallery-scroll::-webkit-scrollbar {
            display: none;
        }

        .gallery-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .faq-answer.open {
            max-height: 200px;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        /* NEW/MODIFIED Styles for Scrolling Gallery */
        .scrolling-gallery-wrapper {
            overflow: hidden;
            -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 1) 10%, rgba(0, 0, 0, 1) 90%, rgba(0, 0, 0, 0));
            mask-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 1) 10%, rgba(0, 0, 0, 1) 90%, rgba(0, 0, 0, 0));
        }

        .scrolling-gallery {
            animation-play-state: running;
        }

        .scrolling-gallery-wrapper:hover .scrolling-gallery {
            animation-play-state: paused;
        }

        .scrolling-gallery-img {
            height: 300px;
            /* Taller images for a better look */
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .scrolling-gallery-img:hover {
            transform: scale(1.05);
        }
    </style>
@endsection
@section('content')
    <main>
        <section class="page-hero-section text-white py-24 md:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Your Culinary Canvas Awaits</h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Step into a world of professional-grade
                    kitchens, designed to bring your culinary vision to life. Flexible, certified, and ready for you.</p>
            </div>
        </section>
        @if($kitchen)
            <section id="booking" class="py-16 lg:py-24 bg-white">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
                        <div class="bg-white p-2 sm:p-4 rounded-lg shadow-2xl section-animate">
                            <img id="mainRoomImage"
                                src="{{$kitchen->primary_image->getUrl()  }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                                alt="Spacious commercial kitchen main view"
                                class="w-full main-gallery-image object-cover rounded-lg mb-4 cursor-pointer"
                                onclick="openModalWithSrc('https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1')">
                            <div id="thumbnailContainer" class="flex space-x-2 overflow-x-auto pb-2 gallery-scroll"></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-2xl section-animate">
                            <h2 class="text-3xl md:text-4xl font-bold text-brand-deep-ash mb-3">The ProChef Station</h2>
                            <p class="text-gray-600 mb-4">Our premier kitchen station, designed for maximum efficiency.
                                Instantly book your slot below.</p>
                            @include('front.common.error-and-message')
                            <div class="flex items-center text-gray-700 mb-4 space-x-4">
                                <span><i class="fas fa-utensils text-accent mr-2"></i> Fully Equipped</span>
                                <span><i class="fas fa-certificate text-accent mr-2"></i> Health Certified</span>
                            </div>
                            <p class="text-4xl font-bold text-brand-deep-ash mb-6">{{ currencyFormatter($kitchen->price) }}<span
                                    class="text-xl font-normal text-gray-500"> / hour</span></p>
                            <form action="{{ route('kitchen-rentals.store', ['product_id' => $kitchen->id]) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="bg-brand-light-blue/50 p-4 rounded-md border border-brand-light-blue">
                                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-3">Book This Kitchen</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                            <input type="date" id="date-picker" name="booking_date" required
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                        </div>
                                        
                                        <div>
                                            <label for="booking-time-display" class="block text-sm font-medium text-gray-700">Time Slot(s)</label>
                                            <div class="custom-multiselect-container relative">
                                                <div id="booking-time-display"
                                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 cursor-pointer flex justify-between items-center min-h-[42px]">
                                                    <span>Select Time Slot(s)</span>
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                                <div id="booking-time-options"
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                                    {{-- Checkbox options will be populated by JavaScript --}}
                                                    <p class="text-gray-500 text-sm px-4 py-2">Please select a date to see available slots.</p>
                                                </div>
                                            </div>
                                            <div id="booking-time-message" class="mt-2 text-sm text-red-600 font-medium"></div>
                                        </div>
                                        <button
                                            class="w-full bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg transform hover:scale-105">
                                            <i class="fas fa-cart-plus mr-2"></i>Add to Cart & Book
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-6 font-pacifico">A Space
                        Built for Chefs</h2>
                    <div class="prose lg:prose-lg max-w-none text-gray-700 leading-relaxed text-left">
                        <p>Welcome to The ProChef Station, our flagship commercial kitchen designed to meet the rigorous
                            demands of today's food professionals. We provide more than just a space to cook; we offer a
                            meticulously planned environment where creativity and efficiency flourish. The layout is
                            optimized for a seamless workflow, from receiving and prep to cooking and plating. Ample
                            stainless steel surfaces provide generous workspace, while non-slip flooring ensures safety
                            during the busiest of services.</p>
                        <p>Whether you're testing recipes for a new restaurant concept, preparing for a large-scale catering
                            event, or producing goods for your packaged food brand, this kitchen provides the professional
                            foundation you need to excel. Let us handle the overhead and maintenance, so you can focus on
                            what you do best: creating exceptional food.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- MOVED: Facility Features Section -->
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Facility
                    Features</h2>
                <div class="max-w-4xl mx-auto bg-gray-50 p-8 rounded-lg shadow-lg grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div class="flex items-center"><i class="fas fa-fan text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Commercial Ventilation</span></div>
                    <div class="flex items-center"><i class="fas fa-fire-extinguisher text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Fire Suppression System</span></div>
                    <div class="flex items-center"><i class="fas fa-sink text-accent text-2xl mr-3"></i><span
                            class="font-semibold">3-Compartment Sinks</span></div>
                    <div class="flex items-center"><i class="fas fa-hand-sparkles text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Hand Washing Stations</span></div>
                    <div class="flex items-center"><i class="fas fa-dumpster text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Waste & Recycling</span></div>
                    <div class="flex items-center"><i class="fas fa-truck-loading text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Loading Dock Access</span></div>
                    <div class="flex items-center"><i class="fas fa-snowflake text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Walk-In Cooler/Freezer</span></div>
                    <div class="flex items-center"><i class="fas fa-box-open text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Dry Storage Areas</span></div>
                    <div class="flex items-center"><i class="fas fa-lock text-accent text-2xl mr-3"></i><span
                            class="font-semibold">24/7 Secure Access</span></div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-4 font-pacifico">How It Works</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-12">Get cooking in 3 simple steps.</p>
                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-mouse-pointer"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">1. Select & Book</h3>
                        <p class="text-gray-600">Choose your desired kitchen, pick a date and time slot, and book instantly
                            online.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-door-open"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">2. Arrive & Prep</h3>
                        <p class="text-gray-600">Arrive at your scheduled time. Your clean, sanitized, and fully-equipped
                            station will be ready for you.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-fire-burner"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">3. Create & Clean</h3>
                        <p class="text-gray-600">Bring your culinary vision to life! When you're done, simply clean your
                            station and you're all set.</p>
                    </div>
                </div>
                <div class="mt-12">
                    <a href="#booking"
                        class="bg-brand-deep-ash text-white font-bold py-3 px-8 rounded-lg text-lg hover:bg-brand-deep-ash-lighter transition duration-300 shadow-lg">Book
                        Your Slot Now</a>
                </div>
            </div>
        </section>

        <!-- UPDATED: Facility Gallery now a scrolling gallery -->
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Facility
                    Gallery</h2>
                <div class="scrolling-gallery-wrapper">
                    <div id="facility-scrolling-gallery" class="flex animate-scroll-x scrolling-gallery">
                        <!-- JS will populate this section -->
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4 text-center italic">Hover to pause. Click image to enlarge.</p>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-brand-light-blue">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Why Choose
                    Our Kitchens?</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-dollar-sign"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Zero Capital Cost</h3>
                        <p class="text-gray-600">Launch your food business without the massive upfront investment of
                            building a kitchen.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-rocket"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Accelerate Growth</h3>
                        <p class="text-gray-600">Quickly scale your production for catering, farmers' markets, or online
                            delivery.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-file-certificate"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Licensed & Certified</h3>
                        <p class="text-gray-600">Operate legally from day one in a fully licensed and
                            health-department-approved facility.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-users"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Join a Community</h3>
                        <p class="text-gray-600">Network and collaborate with a vibrant community of fellow food
                            entrepreneurs.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-brand-deep-ash text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-white mb-12 font-pacifico">From Our Happy Chefs
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-brand-deep-ash-lighter p-8 rounded-lg shadow-lg text-center">
                        <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop&dpr=1"
                            class="w-24 h-24 rounded-full mx-auto -mt-16 mb-4 border-4 border-accent">
                        <div class="text-star text-xl mb-2">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="italic text-brand-light-blue mb-4">"Absolutely top-notch facility. Clean, spacious, and
                            has everything I need for my catering business. A total game-changer!"</p>
                        <p class="font-bold text-lg">Maria Garcia</p>
                        <p class="text-sm text-brand-light-blue-darker">Owner, Sabor Catering</p>
                    </div>
                    <div class="bg-brand-deep-ash-lighter p-8 rounded-lg shadow-lg text-center">
                        <img src="https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop&dpr=1"
                            class="w-24 h-24 rounded-full mx-auto -mt-16 mb-4 border-4 border-accent">
                        <div class="text-star text-xl mb-2">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="italic text-brand-light-blue mb-4">"The flexible booking system allowed me to launch my
                            bakery with minimal risk. The community here is amazing too."</p>
                        <p class="font-bold text-lg">David Chen</p>
                        <p class="text-sm text-brand-light-blue-darker">Founder, The Rolling Pin</p>
                    </div>
                    <div class="bg-brand-deep-ash-lighter p-8 rounded-lg shadow-lg text-center">
                        <img src="https://images.pexels.com/photos/762020/pexels-photo-762020.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop&dpr=1"
                            class="w-24 h-24 rounded-full mx-auto -mt-16 mb-4 border-4 border-accent">
                        <div class="text-star text-xl mb-2">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="far fa-star"></i>
                        </div>
                        <p class="italic text-brand-light-blue mb-4">"Reliable, clean, and professional. ProKitchen
                            provides the perfect prep space for my food truck. Highly recommend!"</p>
                        <p class="font-bold text-lg">Aisha Bello</p>
                        <p class="text-sm text-brand-light-blue-darker">Aisha's Jollof Joint</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative py-24 bg-cover bg-center bg-fixed"
            style="background-image: url('https://images.pexels.com/photos/6646917/pexels-photo-6646917.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');">
            <div class="absolute inset-0 bg-brand-deep-ash opacity-80"></div>
            <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white section-animate">
                <h2 class="text-4xl font-black mb-4">Ready to Fire Up the Stove?</h2>
                <p class="text-xl max-w-2xl mx-auto mb-8 text-brand-light-blue">Your next great dish is waiting to be
                    created. Our kitchens are ready when you are.</p>
                <a href="#booking"
                    class="bg-accent text-brand-deep-ash font-bold py-4 px-10 rounded-lg text-xl hover:bg-accent-darker transition duration-300 shadow-lg transform hover:scale-105">Book
                    Your Kitchen Today</a>
            </div>
        </section>

    </main>

    <!-- Image Modal -->
    <div id="imageModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden bg-black/80 transition-opacity duration-300 opacity-0">
        <div
            class="relative bg-white p-4 rounded-lg shadow-2xl max-w-4xl w-full transition-transform duration-300 transform scale-95">
            <button id="closeModal"
                class="absolute -top-4 -right-4 text-white bg-brand-deep-ash rounded-full w-10 h-10 flex items-center justify-center text-2xl leading-none z-10">&times;</button>
            <img id="modalImage" src="" alt="Enlarged view"
                class="w-full h-auto max-h-[80vh] object-contain rounded">
        </div>
    </div>
@endsection


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Current Year
            const currentYearEl = document.getElementById('currentYear');
            if (currentYearEl) currentYearEl.textContent = new Date().getFullYear();

            // --- MODAL LOGIC ---
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeModalBtn = document.getElementById('closeModal');

            // This function is now globally accessible to be called from onclick attributes
            window.openModalWithSrc = function(src) {
                if (!modal || !modalImage) return;
                modalImage.src = src;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.querySelector('div').classList.remove('scale-95');
                }, 10);
            }

            function closeModal() {
                if (!modal) return;
                modal.classList.add('opacity-0');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => modal.classList.add('hidden'), 300);
            }
            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (modal) modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });


            // --- Main Thumbnail Gallery ---
            const mainImage = document.getElementById('mainRoomImage');
            const thumbnailContainer = document.getElementById('thumbnailContainer');
            const kitchenImageData = [{
                    main: '{{$kitchen->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
                    thumb: '{{$kitchen->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop'
                },
                @foreach ($kitchen->other_images as $media)
                {
                    main: '{{ $media->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
                    thumb: '{{ $media->getUrl() }}?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop'
                },
                @endforeach
               
            ];

            function populateThumbnails(container, targetMainImage, imageData) {
                if (!container) return;
                container.innerHTML = '';
                imageData.forEach(imgData => {
                    const thumb = document.createElement('img');
                    thumb.src = imgData.thumb;
                    thumb.alt = 'Kitchen Thumbnail';
                    thumb.className = 'thumbnail';
                    thumb.dataset.mainSrc = imgData.main;
                    thumb.onclick = () => {
                        targetMainImage.src = thumb.dataset.mainSrc;
                        // Update the onclick for the main image itself
                        targetMainImage.setAttribute('onclick',
                            `openModalWithSrc('${thumb.dataset.mainSrc}')`);
                        container.querySelectorAll('.thumbnail').forEach(t => t.classList.remove(
                            'active'));
                        thumb.classList.add('active');
                    };
                    container.appendChild(thumb);
                });
                if (container.firstChild) container.firstChild.classList.add('active');
            }
            if (mainImage) populateThumbnails(thumbnailContainer, mainImage, kitchenImageData);


            // --- NEW: Facility Scrolling Gallery Logic ---
            const facilityGallery = document.getElementById('facility-scrolling-gallery');
            const facilityImages = [
                '{{$kitchen->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=800',
                 @foreach ($kitchen->other_images as $media)
                    '{{ $media->getUrl() }}?auto=compress&cs=tinysrgb&w=800',
                @endforeach
                'https://images.pexels.com/photos/8299879/pexels-photo-8299879.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/887848/pexels-photo-887848.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/7088481/pexels-photo-7088481.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/6207368/pexels-photo-6207368.jpeg?auto=compress&cs=tinysrgb&w=800'
            ];

            function populateFacilityGallery() {
                if (!facilityGallery) return;
                const imagesToDisplay = [...facilityImages, ...facilityImages]; // Duplicate for seamless loop
                facilityGallery.innerHTML = imagesToDisplay.map(src => `
                    <img src="${src}" alt="Facility Image" class="scrolling-gallery-img w-80 flex-shrink-0 mx-4 rounded-lg shadow-md" onclick="openModalWithSrc('${src}')">
                `).join('');
            }
            populateFacilityGallery();

            // --- Date Picker ---
            const dateInput = document.getElementById('date');
            if (dateInput) {
                dateInput.setAttribute('min', new Date().toISOString().split('T')[0]);
            }

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


                        // --- Booking Form Logic ---
            const datePicker = document.getElementById('date-picker');
            // const durationDropdown = document.getElementById('duration-dropdown'); // Duration is now standard select for cart
            const bookingTimeDisplay = document.getElementById('booking-time-display');
            const bookingTimeOptionsContainer = document.getElementById('booking-time-options');
            let bookingTimeCheckboxes = []; // Will be populated dynamically
            const bookingTimeMessage = document.getElementById('booking-time-message');


            if (datePicker) {
                const today = new Date().toISOString().split('T')[0];
                datePicker.setAttribute('min', today);

                datePicker.addEventListener('change', function () {
                    const selectedDate = this.value;
                    if (selectedDate) {
                        updateBookingTimeSlots(selectedDate);
                    } else {
                        // Clear and hide time slots if date is cleared
                        bookingTimeOptionsContainer.innerHTML = '<p class="text-gray-500 text-sm px-4 py-2">Please select a date to see available slots.</p>';
                        updateBookingTimeDisplay(); // Reset display text
                        bookingTimeOptionsContainer.classList.add('hidden');
                         if(bookingTimeDisplay) bookingTimeDisplay.querySelector('i').classList.remove('rotate-180');

                    }
                });
            }

            function setupBookingTimeCheckboxes() {
                bookingTimeCheckboxes = bookingTimeOptionsContainer.querySelectorAll('input[type="checkbox"]');
                bookingTimeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        updateBookingTimeDisplay();
                        validateBookingTime();
                    });
                });
            }

            if (bookingTimeDisplay && bookingTimeOptionsContainer) {
                bookingTimeDisplay.addEventListener('click', function () {
                    // Only toggle if there are actual options or a message indicating to select a date
                    if (bookingTimeOptionsContainer.children.length > 0) {
                        bookingTimeOptionsContainer.classList.toggle('hidden');
                        this.querySelector('i').classList.toggle('rotate-180');
                    } else {
                        bookingTimeMessage.textContent = "Please select a date first.";
                    }
                });

                document.addEventListener('click', function (event) {
                    if (bookingTimeDisplay && !bookingTimeDisplay.contains(event.target) && bookingTimeOptionsContainer && !bookingTimeOptionsContainer.contains(event.target)) {
                        bookingTimeOptionsContainer.classList.add('hidden');
                         if(bookingTimeDisplay.querySelector('i')) bookingTimeDisplay.querySelector('i').classList.remove('rotate-180');
                    }
                });
            }

            function updateBookingTimeDisplay() {
                if (!bookingTimeDisplay) return;
                const selectedLabels = [];
                bookingTimeCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        // The text content is the checkbox label itself
                        selectedLabels.push(checkbox.parentElement.textContent.trim());
                    }
                });

                const displaySpan = bookingTimeDisplay.querySelector('span');
                if (selectedLabels.length > 0) {
                    displaySpan.textContent = selectedLabels.length === 1 ? selectedLabels[0] : selectedLabels.length + ' slots selected';
                } else {
                    displaySpan.textContent = 'Select Time Slot(s)';
                }
            }

            function validateBookingTime() {
                if (!bookingTimeMessage) return true;
                let oneChecked = false;
                bookingTimeCheckboxes.forEach(cb => {
                    if (cb.checked) oneChecked = true;
                });
                // This validation is for UI feedback; form submission relies on backend
                // if (!oneChecked) {
                // bookingTimeMessage.textContent = "Please select at least one time slot if making a booking.";
                // } else {
                // bookingTimeMessage.textContent = "";
                // }
                return true;
            }

            function updateBookingTimeSlots(selectedDate) {
                if (!bookingTimeOptionsContainer || !selectedDate) return;

                bookingTimeOptionsContainer.innerHTML = '<p class="text-blue-500 text-sm px-4 py-2">Loading slots...</p>'; // Show loading state
                updateBookingTimeDisplay(); // Reset display text to "Select Time Slot(s)"

                // Ensure the dropdown is potentially visible if user clicks display
                bookingTimeOptionsContainer.classList.remove('hidden');
                 if(bookingTimeDisplay) bookingTimeDisplay.querySelector('i').classList.remove('rotate-180');


                fetch(`/schedule/slots?booking_date=${selectedDate}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        bookingTimeOptionsContainer.innerHTML = ''; // Clear loading/old slots
                        if (data.slots && data.slots.length > 0) {
                            data.slots.forEach(slotText => { // Assuming slotText is "9:00 AM - 10:00 AM"
                                const label = document.createElement('label');
                                label.className = 'multiselect-option block px-4 py-2 hover:bg-gray-100 cursor-pointer';
                                // To get a value like "0900-1000", we need to parse/convert slotText
                                // For simplicity, if your getTimeSlots can return {value: "0900-1000", label: "9:00 AM - 10:00 AM"}
                                // it would be easier. Otherwise, we use the display text as value too.
                                // Or, derive value:
                                const timeParts = slotText.match(/(\d{1,2}:\d{2}\s*[AP]M)/g);
                                let valueAttribute = slotText; // Default to full text if parsing fails
                                if (timeParts && timeParts.length === 2) {
                                    const startTime = new Date(`1/1/2000 ${timeParts[0]}`);
                                    const endTime = new Date(`1/1/2000 ${timeParts[1]}`);
                                    valueAttribute = `${String(startTime.getHours()).padStart(2,'0')}${String(startTime.getMinutes()).padStart(2,'0')}-${String(endTime.getHours()).padStart(2,'0')}${String(endTime.getMinutes()).padStart(2,'0')}`;
                                }

                                label.innerHTML = `<input type="checkbox" name="booking_time[]" value="${valueAttribute}" class="mr-2"> ${slotText}`;
                                bookingTimeOptionsContainer.appendChild(label);
                            });
                            setupBookingTimeCheckboxes(); // Re-setup listeners for new checkboxes
                        } else {
                            bookingTimeOptionsContainer.innerHTML = '<p class="text-red-500 text-sm px-4 py-2">No available slots for this date.</br>Select another date</p>';
                        }
                        updateBookingTimeDisplay(); // Update display based on new (empty) selection
                    })
                    .catch(error => {
                        console.error('Error fetching time slots:', error);
                        bookingTimeOptionsContainer.innerHTML = '<p class="text-red-500 text-sm px-4 py-2">Error loading slots. Please try again.</p>';
                        updateBookingTimeDisplay();
                    });
            }

            // Initial call to set up display text
            if (bookingTimeDisplay) {
                 updateBookingTimeDisplay();
            }
        });
    </script>
@endsection
