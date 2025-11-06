@extends('layouts.app')

{{-- SEO Sections --}}
@section('title')
    Kitchen Equipment Rentals - Sejis Rentals
@endsection

@section('keywords')
    rent kitchen equipment, commercial kitchen equipment rental, catering equipment hire, restaurant equipment rental, book
    equipment online
@endsection
@section('description')
    Rent professional-grade, sanitized kitchen equipment by the day, week, or month. Perfect for caterers, pop-ups, and
    food businesses. Browse our selection and book online!
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

        /* Updated Hero Image for Equipment */
        .page-hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('https://images.pexels.com/photos/357577/pexels-photo-357577.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .section-animate {
            opacity: 0;
        }

        .section-animate.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

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

        /* Equipment Card Styling */
        .equipment-card-image {
            height: 250px;
            object-fit: cover;
        }

        /* Modal Styling */
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

        .gallery-scroll::-webkit-scrollbar {
            display: none;
        }

        .gallery-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endsection

@section('content')
    <main>
        <!-- Hero Section -->
        <section class="page-hero-section text-white py-24 md:py-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black mb-4 font-pacifico">Equip Your Culinary Vision
                </h1>
                <p class="text-lg sm:text-xl max-w-3xl mx-auto text-brand-light-blue">Get the professional-grade kitchen
                    equipment you need, exactly when you need it. Flexible rentals for any project, big or small.</p>
            </div>
        </section>

        <!-- Equipment List Section -->
        <section id="equipment-list" class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Our
                    Equipment Selection</h2>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                     <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                @if($equipments) 
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($equipments as $equipment )
                            <!-- Equipment Card 1: Commercial Mixer -->
                            <div
                                class="equipment-card bg-white rounded-lg shadow-lg overflow-hidden flex flex-col transform hover:scale-[1.02] transition-transform duration-300 section-animate">
                                <img src="{{ $equipment->primary_image->getUrl() }}?auto=compress&cs=tinysrgb&w=600"
                                    alt="Commercial Mixer" class="w-full equipment-card-image">
                                <div class="p-6 flex flex-col flex-grow">
                                    <h3 class="text-2xl font-bold text-brand-deep-ash mb-2">{{ $equipment->name }}</h3>
                                    <p class="text-gray-600 mb-4 flex-grow">{{$equipment->intro}}</p>
                                    <p class="text-3xl font-bold text-brand-deep-ash mb-4">{{ currencyFormatter($equipment->price_per_day) }}<span
                                            class="text-lg font-normal text-gray-500"> / day</span></p>
                                    <a href="{{ route('equipment-rentals.show', $equipment->slug) }}"
                                        class="open-equipment-modal mt-auto w-full text-center bg-brand-deep-ash text-white font-bold py-2 px-4 rounded-lg hover:bg-brand-deep-ash-lighter transition duration-300">
                                        View Details & Book
                                    </a>
                                </div>
                            </div>
                        @endforeach


                        <!-- Add more cards as needed -->
                        
                    </div>
                @endif
            </div>
        </section>


        <!-- How It Works Section -->
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-brand-deep-ash mb-4 font-pacifico">How It Works</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-12">Rent your equipment in 3 simple steps.</p>
                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-search-dollar"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">1. Browse & Select</h3>
                        <p class="text-gray-600">Explore our inventory, view details, and find the exact equipment you
                            need for your job.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-calendar-check"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">2. Book Your Dates</h3>
                        <p class="text-gray-600">Select your rental start date and duration, then add to your cart and
                            checkout securely.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="bg-brand-light-blue text-brand-deep-ash rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold mb-4 shadow-lg">
                            <i class="fas fa-truck"></i></div>
                        <h3 class="text-2xl font-semibold text-brand-deep-ash mb-2">3. Pickup or Delivery</h3>
                        <p class="text-gray-600">Choose to pick up your sanitized, ready-to-use equipment or have it
                            delivered right to your location.</p>
                    </div>
                </div>
                <div class="mt-12">
                    <a href="#equipment-list"
                        class="bg-brand-deep-ash text-white font-bold py-3 px-8 rounded-lg text-lg hover:bg-brand-deep-ash-lighter transition duration-300 shadow-lg">Browse
                        Equipment Now</a>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-brand-light-blue">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Why Rent From Us?</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-award"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Pro-Grade Quality</h3>
                        <p class="text-gray-600">Access top-tier, industry-standard brands that deliver reliable performance.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-piggy-bank"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Cost-Effective</h3>
                        <p class="text-gray-600">Avoid high purchase, maintenance, and storage costs. Pay only for what you use.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-calendar-check"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Flexible Terms</h3>
                        <p class="text-gray-600">Rent for a day, a week, or a month. We offer terms that match your project's needs.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-soap"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Clean & Maintained</h3>
                        <p class="text-gray-600">All equipment is professionally cleaned, sanitized, and tested before every rental.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rental Promise Section -->
        <section class="py-16 lg:py-24 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Our Rental
                    Promise</h2>
                <div
                    class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div class="flex items-center"><i class="fas fa-check-circle text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Professionally Maintained</span></div>
                    <div class="flex items-center"><i class="fas fa-pump-soap text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Cleaned & Sanitized</span></div>
                    <div class="flex items-center"><i class="fas fa-calendar-alt text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Flexible Rental Periods</span></div>
                    <div class="flex items-center"><i class="fas fa-truck-moving text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Delivery Available</span></div>
                    <div class="flex items-center"><i class="fas fa-tools text-accent text-2xl mr-3"></i><span
                            class="font-semibold">24/7 Technical Support</span></div>
                    <div class="flex items-center"><i class="fas fa-hand-holding-usd text-accent text-2xl mr-3"></i><span
                            class="font-semibold">Transparent Pricing</span></div>
                </div>
            </div>
        </section>

         <section class="relative py-24 bg-cover bg-center bg-fixed" style="background-image: url('https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');">
            <div class="absolute inset-0 bg-brand-deep-ash opacity-80"></div>
            <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white section-animate">
                <h2 class="text-4xl font-black mb-4">Have an upcoming event or project?</h2>
                <p class="text-xl max-w-2xl mx-auto mb-8 text-brand-light-blue">Get the professional tools you need to make it a success. Explore our rental catalog now.</p>
                <a href="#equipment-list" class="bg-accent text-brand-deep-ash font-bold py-4 px-10 rounded-lg text-xl hover:bg-accent-darker transition duration-300 shadow-lg transform hover:scale-105">View Equipment Catalog</a>
            </div>
        </section>

        <!-- Equipment Gallery Section -->
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">
                    Equipment Gallery</h2>
                <div class="scrolling-gallery-wrapper">
                    <div id="facility-scrolling-gallery" class="flex animate-scroll-x scrolling-gallery">
                        <!-- JS will populate this section -->
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4 text-center italic">Hover to pause. Click image to enlarge.</p>
            </div>
        </section>

        <!-- Use Cases Section -->
        <section class="py-16 lg:py-24 bg-brand-light-blue">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 font-pacifico">Perfect
                    For Every Need</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-store-alt"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Pop-Ups & Food Stalls</h3>
                        <p class="text-gray-600">Launch your pop-up or market stall without the long-term equipment
                            commitment.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-utensils"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Catering Events</h3>
                        <p class="text-gray-600">Scale up for large weddings, corporate events, or private parties with
                            extra ovens, warmers, and more.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-flask"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Recipe Testing</h3>
                        <p class="text-gray-600">Test and perfect your recipes on professional-grade equipment before
                            investing.</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-lg shadow-lg text-center transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="text-accent text-5xl mb-4"><i class="fas fa-calendar-day"></i></div>
                        <h3 class="text-xl font-bold text-brand-deep-ash mb-2">Seasonal & Peak Times</h3>
                        <p class="text-gray-600">Handle holiday rushes or busy seasons by supplementing your existing
                            kitchen line-up.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-16 lg:py-24 bg-brand-deep-ash text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 section-animate">
                <h2 class="text-3xl sm:text-4xl font-bold text-center text-white mb-12 font-pacifico">From Our Happy
                    Renters
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-brand-deep-ash-lighter p-8 rounded-lg shadow-lg text-center">
                        <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop&dpr=1"
                            class="w-24 h-24 rounded-full mx-auto -mt-16 mb-4 border-4 border-accent">
                        <div class="text-star text-xl mb-2">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="italic text-brand-light-blue mb-4">"The convection oven I rented was in perfect
                            condition and was a lifesaver for my catering gig. Delivery was right on time!"</p>
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
                        <p class="italic text-brand-light-blue mb-4">"Renting a mixer allowed me to test my large-batch
                            recipes before buying. The process was so easy. Will definitely use again."</p>
                        <p class="font-bold text-lg">David Chen</p>
                        <p class="text-sm text-brand-light-blue-darker">Founder, The Rolling Pin</p>
                    </div>
                    <div class="bg-brand-deep-ash-lighter p-8 rounded-lg shadow-lg text-center">
                        <img src="https://images.pexels.com/photos/762020/pexels-photo-762020.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop&dpr=1"
                            class="w-24 h-24 rounded-full mx-auto -mt-16 mb-4 border-4 border-accent">
                        <div class="text-star text-xl mb-2">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="italic text-brand-light-blue mb-4">"Needed an extra fryer for a weekend festival. Sejis
                            Rentals had exactly what I needed, it was clean, and pickup was a breeze."</p>
                        <p class="font-bold text-lg">Aisha Bello</p>
                        <p class="text-sm text-brand-light-blue-darker">Aisha's Jollof Joint</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 bg-cover bg-center bg-fixed"
            style="background-image: url('https://images.pexels.com/photos/4049870/pexels-photo-4049870.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');">
            <div class="absolute inset-0 bg-brand-deep-ash opacity-80"></div>
            <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center text-white section-animate">
                <h2 class="text-4xl font-black mb-4">Ready to Get Cooking?</h2>
                <p class="text-xl max-w-2xl mx-auto mb-8 text-brand-light-blue">Find the professional equipment you need to
                    succeed. Our inventory is clean, certified, and ready to go.</p>
                <a href="#equipment-list"
                    class="bg-accent text-brand-deep-ash font-bold py-4 px-10 rounded-lg text-xl hover:bg-accent-darker transition duration-300 shadow-lg transform hover:scale-105">Browse
                    Our Inventory</a>
            </div>
        </section>

    </main>

    <!-- Image Modal (For Gallery) -->
    <div id="imageModal"
        class="modal hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80">
        <div
            class="modal-content relative bg-white p-4 rounded-lg shadow-2xl max-w-4xl w-full">
            <button id="closeImageModal"
                class="absolute -top-4 -right-4 text-white bg-brand-deep-ash rounded-full w-10 h-10 flex items-center justify-center text-2xl leading-none z-10">&times;</button>
            <img id="modalImage" src="" alt="Enlarged view"
                class="w-full h-auto max-h-[80vh] object-contain rounded">
        </div>
    </div>

    <!-- Equipment Details Modal -->
    <div id="equipmentModal"
        class="modal hidden fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/80">
        <div
            class="modal-content relative bg-white p-6 rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto section-animate">
            <button id="closeEquipmentModal"
                class="absolute -top-4 -right-4 text-white bg-brand-deep-ash rounded-full w-10 h-10 flex items-center justify-center text-2xl leading-none z-10">&times;</button>
            
            <form action="{{ route('equipment-rentals.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" id="modal_product_id" name="product_id">
                <input type="hidden" id="modal_product_name" name="product_name">
                <input type="hidden" id="modal_product_price" name="price_per_day">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Side: Image -->
                    <div>
                        <img id="modal_equipment_image"
                            src="https://placehold.co/600x400/eeeeee/cccccc?text=Equipment"
                            alt="Equipment Details" class="w-full h-80 object-cover rounded-lg mb-4">
                    </div>

                    <!-- Right Side: Details & Form -->
                    <div class="flex flex-col">
                        <h2 id="modal_equipment_name" class="text-3xl md:text-4xl font-bold text-brand-deep-ash mb-3">
                            Equipment Name</h2>
                        <p id="modal_equipment_description" class="text-gray-600 mb-4 flex-grow">Equipment description goes
                            here. This will be a more detailed overview of the item's specs and features.</p>

                        <p id="modal_equipment_price_display" class="text-4xl font-bold text-brand-deep-ash mb-6">$0<span
                                class="text-xl font-normal text-gray-500"> / day</span></p>

                        <div class="bg-brand-light-blue/50 p-4 rounded-md border border-brand-light-blue mt-auto">
                            <h3 class="text-xl font-semibold text-brand-deep-ash mb-3">Book This Equipment</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Rental Start Date</label>
                                    <input type="date" id="start_date" name="start_date" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>

                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>

                                <div>
                                    <label for="rental_duration" class="block text-sm font-medium text-gray-700">Rental Duration (in days)</label>
                                    <input type="number" id="rental_duration" name="rental_duration" value="1" min="1" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent sm:text-sm">
                                </div>
                                
                                <button
                                    class="w-full bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg transform hover:scale-105">
                                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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


            // --- Facility/Equipment Scrolling Gallery ---
            const facilityGallery = document.getElementById('facility-scrolling-gallery');
            const facilityImages = [
                'https://images.pexels.com/photos/45204/kitchen-food-preparation-kitchen-utensils-45204.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/298335/pexels-photo-298335.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/8134105/pexels-photo-8134105.jpeg?auto=compress&cs=tinysrgb&w=800',
                'https://images.pexels.com/photos/3622479/pexels-photo-3622479.jpeg?auto=compress&cs=tinysrgb&w=800',
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
