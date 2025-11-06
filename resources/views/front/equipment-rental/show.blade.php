@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    {{-- This will be dynamic, passed from the controller --}}
    {{ $equipment->name ?? 'Equipment Details' }} - Sejis Rentals
@endsection

@section('keywords')
    rent {{ $equipment->name ?? 'kitchen equipment' }}, commercial kitchen equipment rental, catering equipment hire
@endsection
@section('description')
    Rent the {{ $equipment->name ?? 'professional kitchen equipment' }}. Sanitized, maintained, and ready for your event
    or pop-up. Book online today!
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

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        /* Gallery styles from your original index.blade.php */
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

        /* Scrolling "Related" Gallery */
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
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .scrolling-gallery-img:hover {
            transform: scale(1.05);
        }

        /* Modal Styling for gallery pop-up */
        .modal {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-content {
            transition: transform 0.3s ease;
        }

        .modal.hidden {
            visibility: hidden;
            opacity: 0;
        }

        .modal.hidden .modal-content {
            transform: scale(0.95);
        }

        .modal.visible {
            visibility: visible;
            opacity: 1;
        }

        .modal.visible .modal-content {
            transform: scale(1);
        }
    </style>
@endsection

@section('content')
    <main>
        <section class="page-hero-section-simple bg-white py-6 border-b border-gray-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 animate-fade-in-up">
                <nav class="text-sm font-medium text-gray-500 mb-2" aria-label="Breadcrumb">
                    <ol class="list-none p-0 inline-flex">
                        <li class="flex items-center">
                            <a href="/" class="hover:text-accent">Home</a>
                            <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        </li>
                        <li class="flex items-center">
                            <a href="{{ route('equipment-rentals.index') }}" class="hover:text-accent">Equipment</a>
                            {{-- Assumes you have a named route 'equipment.index' for the list page --}}
                            <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        </li>
                        <li class="flex items-center text-brand-deep-ash font-semibold">
                            {{ $equipment->name ?? 'Details' }}
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl sm:text-4xl md:text-3xl font-bold text-brand-deep-ash">
                    {{ $equipment->name ?? 'Equipment Details' }}
                </h1>
            </div>
        </section>

        <section id="equipment-details" class="py-16 lg:py-5 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">

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

                {{-- Mocking $equipment object if it doesn't exist for placeholder --}}
                @php
                    if (!isset($equipment)) {
                        $equipment = (object) [
                            'id' => 'EQ-PLC-001',
                            'name' => 'Placeholder Equipment',
                            'description' => 'This is a placeholder description for the equipment. It highlights the features and specifications. Replace this with data from your controller.',
                            'price_per_day' => 99,
                            'primary_image' =>
                                (object) ['getUrl' => fn() => 'https://placehold.co/800x600/eeeeee/cccccc?text=Main+Image'],
                            'other_images' => [
                                (object) ['getUrl' => fn() => 'https://placehold.co/800x600/e8e8e8/b8b8b8?text=Image+2'],
                                (object) ['getUrl' => fn() => 'https://placehold.co/800x600/e0e0e0/a0a0a0?text=Image+3'],
                            ],
                        ];
                        // Helper function for placeholder
                        if (!function_exists('currencyFormatter')) {
                            function currencyFormatter($amount)
                            {
                                return '$' . number_format($amount, 2);
                            }
                        }
                    }
                    //dd($equipment->primary_image);
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
                    <div class="bg-white p-2 sm:p-4 rounded-lg shadow-xl section-animate">
                        <img id="mainEquipmentImage"
                            src="{{ $equipment->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                            alt="{{ $equipment->name }} main view"
                            class="w-full main-gallery-image object-cover rounded-lg mb-4 cursor-pointer"
                            onclick="openModalWithSrc('{{ $equipment->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1')">
                        <div id="thumbnailContainer" class="flex space-x-2 overflow-x-auto pb-2 gallery-scroll">
                            {{-- Thumbnails will be populated by JavaScript --}}
                        </div>

                        <div class="prose lg:prose-lg max-w-none text-gray-700 leading-relaxed mt-8 p-4">
                            {!! $equipment->intro ?? 'Full description not available.' !!}
                        </div>
                    </div>

                    {{-- ID and scroll-mt-16 added here for the CTA link --}}
                    <div id="booking-form-section" class="bg-white p-6 rounded-lg shadow-xl section-animate scroll-mt-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-brand-deep-ash mb-3">
                            {{ $equipment->name }}</h2>

                        <p class="text-4xl font-bold text-brand-deep-ash mb-6">
                            {{ currencyFormatter($equipment->price_per_day) }}<span
                                class="text-xl font-normal text-gray-500"> / day</span></p>

                        {{-- This is the Product Introduction section --}}
                        <div class="prose lg:prose-lg max-w-none text-gray-700 leading-relaxed mb-8">
                            {!! $equipment->intro !!}
                        </div>

                        <form action="{{ route('equipment-rentals.store') }}" method="POST"
                            class="space-y-4 bg-brand-light-blue/50 p-6 rounded-md border border-brand-light-blue">
                            @csrf
                            <input type="hidden" id="product_id" name="product_id" value="{{ $equipment->id }}">
                            <input type="hidden" id="product_name" name="product_name"
                                value="{{ $equipment->name }}">
                            <input type="hidden" id="price_per_day" name="price_per_day"
                                value="{{ $equipment->price_per_day }}">

                            <h3 class="text-xl font-semibold text-brand-deep-ash mb-3">Book This Equipment</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Rental Start
                                        Date</label>
                                    <input type="date" id="start_date" name="start_date" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>

                                {{-- [START] MODIFICATION --}}
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">Rental End
                                        Date</label>
                                    <input type="date" id="end_date" name="end_date" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>
                                {{-- [END] MODIFICATION --}}

                                <div>
                                    <label for="quantity"
                                        class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>

                                {{-- [START] MODIFICATION --}}
                                <div>
                                    <label for="rental_duration" class="block text-sm font-medium text-gray-700">Rental
                                        Duration (in days)</label>
                                    <input type="number" id="rental_duration" name="rental_duration" value="1"
                                        min="1" required readonly
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm bg-gray-100">
                                </div>
                                {{-- [END] MODIFICATION --}}

                                <button
                                    class="w-full bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg transform hover:scale-105">
                                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">
                    Product Description
                </h2>
                <div class="max-w-4xl mx-auto prose lg:prose-xl text-gray-700 leading-relaxed">
                    {!! $equipment->description ?? 'Full description not available.' !!}
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-brand-light-blue">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-4 font-pacifico">How It Works</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-12">Rent your equipment in 3 simple steps.</p>
                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-white text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-search-dollar"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">1. Select Item</h3>
                        <p class="text-gray-600">You've found your item. Now just select your dates and quantity right
                            here on this page.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-white text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-calendar-check"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">2. Book Your Dates</h3>
                        <p class="text-gray-600">Add the equipment to your cart and checkout securely to confirm your
                            rental reservation.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-white text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-truck"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">3. Pickup or Delivery</h3>
                        <p class="text-gray-600">Choose to pick up your sanitized, ready-to-use equipment or have it
                            delivered right to your location.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">
                    Frequently Asked Questions</h2>
                <div class="max-w-4xl mx-auto space-y-8">
                    <div>
                        <h4 class="text-xl font-bold text-brand-deep-ash mb-2">What is the condition of the equipment?
                        </h4>
                        <p class="text-gray-700 leading-relaxed">All our equipment is professionally maintained,
                            tested, and deep-cleaned after every rental. We guarantee everything will be in perfect
                            working order and sanitized for your use.</p>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-brand-deep-ash mb-2">Is delivery available for this item?</h4>
                        <p class="text-gray-700 leading-relaxed">Yes, we offer delivery and pickup services across the
                            area. You can also choose to pick up the item from our warehouse to save on fees. Delivery
                            options and costs can be calculated at checkout.</p>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-brand-deep-ash mb-2">What if I need the equipment for longer
                            than I booked?</h4>
                        <p class="text-gray-700 leading-relaxed">We understand plans can change. Please contact us as
                            soon as you know you'll need an extension. We will check availability and, if possible,
                            extend your rental period. Additional daily rates will apply.</p>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-brand-deep-ash mb-2">What is your cancellation policy?</h4>
                        <p class="text-gray-700 leading-relaxed">You can cancel free of charge up to 72 hours before
                            your rental start date. Cancellations within 72 hours may be subject to a fee. Please see
                            our full rental agreement for details.</p>
                    </div>
                </div>
            </div>
        </section>


        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">
                    Other Equipment You Might Like</h2>
                <div class="scrolling-gallery-wrapper">
                    <div id="facility-scrolling-gallery" class="flex animate-scroll-x scrolling-gallery">
                        {{-- JS will populate this section --}}
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4 text-center italic">Hover to pause. Click image to enlarge.</p>
            </div>
        </section>

        <section class="relative py-24 bg-cover bg-center bg-fixed"
            style="background-image: url('https://images.pexels.com/photos/4049870/pexels-photo-4049870.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');">
            <div class="absolute inset-0 bg-brand-deep-ash opacity-80"></div>
            <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white section-animate">
                <h2 class="text-4xl font-black mb-4">Ready to Book This Item?</h2>
                <p class="text-xl max-w-2xl mx-auto mb-8 text-brand-light-blue">Select your dates and add this equipment to
                    your cart to get started.</p>
                <a href="#booking-form-section"
                    class="bg-accent text-brand-deep-ash font-bold py-4 px-10 rounded-lg text-xl hover:bg-accent-darker transition duration-300 shadow-lg transform hover:scale-105">
                    Book This Equipment Now
                </a>
            </div>
        </section>

    </main>

    <div id="imageModal"
        class="modal hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80">
        <div class="modal-content relative bg-white p-4 rounded-lg shadow-2xl max-w-4xl w-full">
            <button id="closeImageModal"
                class="absolute -top-4 -right-4 text-white bg-brand-deep-ash rounded-full w-10 h-10 flex items-center justify-center text-2xl leading-none z-10">&times;</button>
            <img id="modalImage" src="" alt="Enlarged view"
                class="w-full h-auto max-h-[80vh] object-contain rounded">
        </div>
    </div>
@endsection


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Modal Helpers ---
            const showModal = (modal) => {
                if (!modal) return;
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.add('visible'), 10);
            };

            const closeModal = (modal) => {
                if (!modal) return;
                modal.classList.remove('visible');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            // --- Image Modal (For Gallery) ---
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeImageModalBtn = document.getElementById('closeImageModal');

            window.openModalWithSrc = function(src) {
                if (!imageModal || !modalImage) return;
                modalImage.src = src;
                showModal(imageModal);
            }

            if (closeImageModalBtn) closeImageModalBtn.addEventListener('click', () => closeModal(imageModal));
            if (imageModal) imageModal.addEventListener('click', (e) => {
                if (e.target === imageModal) closeModal(imageModal);
            });

            // Global Escape key listener
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    if (imageModal && imageModal.classList.contains('visible')) {
                        closeModal(imageModal);
                    }
                }
            });

            // --- Main Thumbnail Gallery ---
            const mainImage = document.getElementById('mainEquipmentImage');
            const thumbnailContainer = document.getElementById('thumbnailContainer');

            // This data must be passed from the controller, matching the $equipment object
            const equipmentImageData = [
                @if (isset($equipment) && $equipment->primary_image)
                    {
                        main: '{{ $equipment->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
                        thumb: '{{ $equipment->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop'
                    },
                @endif
                @if (isset($equipment) && $equipment->other_images)
                    @foreach ($equipment->other_images as $media)
                        {
                            main: '{{ $media->getUrl() }}?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
                            thumb: '{{ $media->getUrl() }}?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop'
                        },
                    @endforeach
                @endif
            ];

            function populateThumbnails(container, targetMainImage, imageData) {
                if (!container || !targetMainImage) return;
                container.innerHTML = '';
                imageData.forEach(imgData => {
                    const thumb = document.createElement('img');
                    thumb.src = imgData.thumb;
                    thumb.alt = 'Equipment Thumbnail';
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
            populateThumbnails(thumbnailContainer, mainImage, equipmentImageData);


            // --- [START] MODIFICATION: Date Picker and Duration Calculation ---
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const durationInput = document.getElementById('rental_duration');

            if (startDateInput && endDateInput && durationInput) {
                const today = new Date().toISOString().split('T')[0];

                // 1. Set initial min date and value for start date
                startDateInput.setAttribute('min', today);
                startDateInput.value = today;

                // 2. Set initial min date and value for end date
                endDateInput.setAttribute('min', today);
                endDateInput.value = today;

                // 3. Define the calculation function
                function calculateDuration() {
                    const startDateValue = startDateInput.value;
                    const endDateValue = endDateInput.value;

                    if (startDateValue && endDateValue) {
                        const startDate = new Date(startDateValue);
                        const endDate = new Date(endDateValue);

                        // 4. Check if end date is before start date
                        if (endDate < startDate) {
                            // If so, set end date to be the same as start date
                            endDateInput.value = startDateValue;
                        }

                        // 5. Recalculate dates after potential correction
                        const finalStartDate = new Date(startDateInput.value);
                        const finalEndDate = new Date(endDateInput.value);

                        // 6. Calculate difference in time (in milliseconds)
                        const diffInTime = finalEndDate.getTime() - finalStartDate.getTime();

                        // 7. Calculate difference in days and add 1 (for inclusive duration)
                        // e.g., Oct 10 to Oct 11 is 2 days. (diff is 1) + 1 = 2.
                        const diffInDays = Math.round(diffInTime / (1000 * 60 * 60 * 24));
                        const rentalDuration = diffInDays + 1;

                        // 8. Update the duration input
                        if (rentalDuration >= 1) {
                            durationInput.value = rentalDuration;
                        } else {
                            durationInput.value = 1; // Failsafe
                        }
                    }
                }

                // 9. Add event listeners
                startDateInput.addEventListener('change', () => {
                    // When start date changes, update end date's min attribute
                    endDateInput.setAttribute('min', startDateInput.value);
                    // Recalculate
                    calculateDuration();
                });

                endDateInput.addEventListener('change', calculateDuration);

                // 10. Initial calculation on load (since we set default values)
                calculateDuration();
            }
            // --- [END] MODIFICATION ---


            // --- Related Equipment Scrolling Gallery ---
            const facilityGallery = document.getElementById('facility-scrolling-gallery');
            // This would ideally be populated by a $relatedEquipment variable
            const facilityImages = [
                'https://images.pexels.com/photos/45204/kitchen-food-preparation-kitchen-utensils-45204.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/298335/pexels-photo-298335.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/8134105/pexels-photo-8134105.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.Pexels.com/photos/3622479/pexels-photo-3622479.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/6207368/pexels-photo-6207368.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/5632398/pexels-photo-5632398.jpeg?auto=compress&cs=tinysrgb&w=800'
            ];

            function populateFacilityGallery() {
                if (!facilityGallery) return;
                const imagesToDisplay = [...facilityImages, ...facilityImages]; // Duplicate for seamless loop
                facilityGallery.innerHTML = imagesToDisplay.map(src => `
                    <img src="${src}" alt="Facility Image" class="scrolling-gallery-img w-80 flex-shrink-0 mx-4 rounded-lg shadow-md" onclick="openModalWithSrc('${src}')">
                `).join('');
            }
            populateFacilityGallery();

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
