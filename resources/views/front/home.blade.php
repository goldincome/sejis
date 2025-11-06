@extends('layouts.app')
@section('title')
    Sejis Kitchen Rentals - Commercial Kitchen & Equipment Hire
@endsection
@section('description')
    Rent professional commercial kitchen spaces, high-quality pots, plates, and cutlery. Flexible, affordable solutions for chefs, caterers, and food entrepreneurs in Woolwich Uk. Book your space today!"
@endsection
@section('keywords')
    commercial kitchen rental, shared kitchen space, ghost kitchen, commissary kitchen for rent, hourly kitchen rental, culinary workspace, food business incubator, catering kitchen hire, pot rental, plate rental, cutlery hire, restaurant equipment rental, Woolwich kitchen rental, food startup resources
@endsection

@section('content')
    <section class="hero-section text-white py-24 md:py-25">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 font-pacifico animate-fade-in-down">Rent Your Perfect Kitchen & Equipment Today</h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-10 max-w-3xl mx-auto text-brand-light-blue animate-fade-in-up">Access fully-equipped commercial kitchens and premium equipment rentals. Perfect for chefs, caterers, and food entrepreneurs ready to create, innovate, and grow.</p>
            <a href="{{ route('kitchen-rentals.index') }}" class="bg-accent text-white font-bold py-3 px-8 sm:py-4 sm:px-10 rounded-lg text-lg sm:text-xl hover:bg-accent-darker transition duration-300 transform hover:scale-105 animate-bounce-custom">Get Started Today</a>
        </div>
    </section>

    <section id="services" class="py-16 lg:py-24 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-4 font-pacifico">Our Core Services</h2>
            <p class="text-center text-gray-600 mb-12 sm:mb-16 text-lg max-w-2xl mx-auto">Flexible and professional solutions designed for your culinary success. Rent top-tier kitchens and essential equipment with ease.</p>
            <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
                <div class="bg-gray-50 rounded-xl shadow-xl overflow-hidden transform hover:shadow-2xl transition-shadow duration-300 flex flex-col">
                    <img src="https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Modern commercial kitchen with stainless steel appliances" class="w-full service-card-img">
                    <div class="p-6 lg:p-8 flex flex-col flex-grow">
                        <h3 class="text-2xl lg:text-3xl font-semibold text-brand-deep-ash mb-3"><i class="fas fa-kitchen-set mr-2 text-accent"></i>Commercial Kitchen Rental</h3>
                        <p class="text-gray-700 mb-6 flex-grow">State-of-the-art, health-certified kitchen spaces available by the hour, day, or month. Ideal for food startups, pop-ups, catering, and product development. Equipped for efficiency and creativity.</p>
                        <a href="{{ route('kitchen-rentals.index') }}" class="mt-auto inline-block bg-brand-deep-ash text-white font-semibold py-3 px-6 rounded-lg hover:bg-brand-deep-ash-lighter transition duration-300 text-center">Book Now! <i class="fas fa-arrow-right ml-2"></i></a>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl shadow-xl overflow-hidden transform hover:shadow-2xl transition-shadow duration-300 flex flex-col">
                    <img src="{{ asset('images/cutlery.jpg') }}" alt="Assortment of clean pots, plates, and cutlery ready for rental" class="w-full service-card-img">
                    <div class="p-6 lg:p-8 flex flex-col flex-grow">
                        <h3 class="text-2xl lg:text-3xl font-semibold text-brand-deep-ash mb-3"><i class="fas fa-utensils mr-2 text-accent"></i>Pot, Plate & Cutlery Rental</h3>
                        <p class="text-gray-700 mb-6 flex-grow">Rent high-quality cookware, elegant tableware, and professional cutlery for your events, catering services, or temporary kitchen needs. Flexible packages available.</p>
                        <a href="{{ route('equipment-rentals.index') }}" class="mt-auto inline-block bg-brand-deep-ash text-white font-semibold py-3 px-6 rounded-lg hover:bg-brand-deep-ash-lighter transition duration-300 text-center">Browse Equipment <i class="fas fa-arrow-right ml-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-24 bg-brand-light-blue">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 sm:mb-16 font-pacifico">Why Choose ProKitchen?</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Affordable & Flexible</h3>
                    <p class="text-gray-600">Competitive pricing with hourly, daily, and monthly rates. Pay only for what you need, when you need it.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-tools"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Professional Equipment</h3>
                    <p class="text-gray-600">Access commercial-grade, well-maintained appliances and tools to ensure quality and efficiency.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-certificate"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Certified & Compliant</h3>
                    <p class="text-gray-600">Our kitchens meet rigorous health and safety standards, providing a worry-free production environment.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-map-marker-alt"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Convenient Location</h3>
                    <p class="text-gray-600">Easily accessible facilities designed to streamline your operations and logistics. (Specify if applicable)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-lightbulb"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Support for Growth</h3>
                    <p class="text-gray-600">We're more than just a rental space; we're a partner in your culinary journey, offering resources and support.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center transform hover:scale-105 transition-transform duration-300">
                    <div class="text-accent text-4xl mb-4"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Easy Booking</h3>
                    <p class="text-gray-600">Simple and transparent online booking system to reserve your kitchen space and equipment hassle-free.</p>
                </div>
            </div>
            <div class="text-center mt-12 sm:mt-16">
                <a href="{{ route('about-us') }}" class="bg-brand-deep-ash text-white font-semibold py-3 px-8 rounded-lg hover:bg-brand-deep-ash-lighter transition duration-300 text-lg">Learn More About Us</a>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-24 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 sm:mb-16 font-pacifico">Getting Started is Easy</h2>
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="flex flex-col items-center">
                    <div class="bg-accent text-brand-deep-ash rounded-full w-20 h-20 flex items-center justify-center text-3xl font-bold mb-4 shadow-lg">1</div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Browse & Select</h3>
                    <p class="text-gray-600">Explore our kitchen spaces and equipment. Choose what fits your needs and schedule.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="bg-accent text-brand-deep-ash rounded-full w-20 h-20 flex items-center justify-center text-3xl font-bold mb-4 shadow-lg">2</div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Book Online</h3>
                    <p class="text-gray-600">Use our simple online system to reserve your dates and times. Instant confirmation.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="bg-accent text-brand-deep-ash rounded-full w-20 h-20 flex items-center justify-center text-3xl font-bold mb-4 shadow-lg">3</div>
                    <h3 class="text-xl font-semibold text-brand-deep-ash mb-2">Create & Cook!</h3>
                    <p class="text-gray-600">Arrive at your scheduled time, access your clean and equipped space, and bring your culinary creations to life.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="featured-items" class="py-16 md:py-24 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-dark-charcoal mb-12 md:mb-16">Explore Our Popular Options</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="rounded-lg shadow-xl overflow-hidden group">
                    <img src="https://images.pexels.com/photos/3771120/pexels-photo-3771120.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Spacious Commercial Kitchen" class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.onerror=null; this.src='https://placehold.co/600x400/cccccc/ffffff?text=Kitchen+1';">
                    <div class="p-6 bg-white">
                        <h3 class="text-xl font-semibold text-dark-charcoal mb-2">The Culinary Hub - Central City</h3>
                        <p class="text-sm text-gray-custom mb-3">Perfect for large-scale production or team cooking events. Fully HACCP compliant.</p>
                        <a href="{{ route('kitchen-rentals.index') }}" class="text-primary-orange font-semibold hover:underline">View Details & Availability <i class="fas fa-arrow-right text-xs ml-1"></i></a>
                    </div>
                </div>
                <div class="rounded-lg shadow-xl overflow-hidden group">
                    <img src="{{ asset('images/kitchen-mixer.jpeg') }}" alt="Industrial Stand Mixer" class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.onerror=null; this.src='https://placehold.co/600x400/cccccc/ffffff?text=Mixer';">
                    <div class="p-6 bg-white">
                        <h3 class="text-xl font-semibold text-dark-charcoal mb-2">Heavy-Duty Stand Mixer (20 Qt)</h3>
                        <p class="text-sm text-gray-custom mb-3">Ideal for bakeries and high-volume mixing tasks. Multiple attachments available.</p>
                        <a href="{{ route('equipment-rentals.index') }}" class="text-primary-orange font-semibold hover:underline">Rent This Equipment <i class="fas fa-arrow-right text-xs ml-1"></i></a>
                    </div>
                </div>
                <div class="rounded-lg shadow-xl overflow-hidden group">
                    <img src="{{ asset('images/event-rentals.jpg') }}" alt="Compact Ghost Kitchen" class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.onerror=null; this.src='https://placehold.co/600x400/cccccc/ffffff?text=Kitchen+2';">
                    <div class="p-6 bg-white">
                        <h3 class="text-xl font-semibold text-dark-charcoal mb-2">Pot, Plate & Cutlery</h3>
                        <p class="text-sm text-gray-custom mb-3">Rent high-quality cookware, elegant tableware, and professional cutlery for your events.</p>
                        <a href="{{ route('equipment-rentals.index') }}" class="text-primary-orange font-semibold hover:underline">Check Availability <i class="fas fa-arrow-right text-xs ml-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-24 bg-gray-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 sm:mb-16 font-pacifico">What Our Clients Say</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg?auto=compress&cs=tinysrgb&w=126&h=126&fit=crop&dpr=1" alt="Happy client - Chef Maria" class="testimonial-img mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"ProKitchen has been a game-changer for my catering business. The facilities are top-notch and the booking process is so simple!"</p>
                    <p class="font-semibold text-brand-deep-ash">- Chef Maria R.</p>
                    <p class="text-sm text-gray-500">Owner, Maria's Catering</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=126&h=126&fit=crop&dpr=1" alt="Satisfied client - Baker John" class="testimonial-img mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"The flexibility of renting kitchen space here allowed me to launch my bakery without the huge upfront costs. Highly recommend!"</p>
                    <p class="font-semibold text-brand-deep-ash">- John B.</p>
                    <p class="text-sm text-gray-500">Founder, The Sweet Spot Bakery</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg md:col-span-2 lg:col-span-1">
                     <img src="https://images.pexels.com/photos/762020/pexels-photo-762020.jpeg?auto=compress&cs=tinysrgb&w=126&h=126&fit=crop&dpr=1" alt="Pleased client - Food Truck Owner Aisha" class="testimonial-img mx-auto mb-4">
                    <p class="text-gray-600 italic mb-4">"Needed reliable equipment for a big festival, and ProKitchen delivered. Great quality and service for my food truck."</p>
                    <p class="font-semibold text-brand-deep-ash">- Aisha K.</p>
                    <p class="text-sm text-gray-500">Aisha's Eats On Wheels</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-24 bg-brand-light-blue">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-center text-brand-deep-ash mb-12 sm:mb-16 font-pacifico">Frequently Asked Questions</h2>
            <div class="max-w-3xl mx-auto space-y-4">
                <div class="bg-white rounded-lg shadow-md">
                    <button class="faq-question w-full flex justify-between items-center text-left p-5 focus:outline-none">
                        <span class="text-lg font-medium text-brand-deep-ash">What are the minimum rental hours?</span>
                        <svg class="w-6 h-6 text-brand-deep-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="faq-answer hidden p-5 border-t border-gray-200">
                        <p class="text-gray-700">Our kitchen rentals typically start with a minimum of 4-hour blocks. We also offer flexible daily, weekly, and monthly rates to suit more extensive needs. Please check our booking page for specific options.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md">
                    <button class="faq-question w-full flex justify-between items-center text-left p-5 focus:outline-none">
                        <span class="text-lg font-medium text-brand-deep-ash">Is insurance required to rent a kitchen?</span>
                        <svg class="w-6 h-6 text-brand-deep-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="faq-answer hidden p-5 border-t border-gray-200">
                        <p class="text-gray-700">Yes, for the safety and protection of all our clients and our facility, general liability insurance is required. We can provide guidance on obtaining affordable coverage if you don't already have it.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md">
                    <button class="faq-question w-full flex justify-between items-center text-left p-5 focus:outline-none">
                        <span class="text-lg font-medium text-brand-deep-ash">Can I store ingredients or my own equipment on-site?</span>
                        <svg class="w-6 h-6 text-brand-deep-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="faq-answer hidden p-5 border-t border-gray-200">
                        <p class="text-gray-700">We offer various storage solutions, including dry, refrigerated, and freezer space, subject to availability and an additional fee. You may also be able to bring in small, pre-approved personal equipment. Please contact us to discuss your specific storage needs.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md">
                    <button class="faq-question w-full flex justify-between items-center text-left p-5 focus:outline-none">
                        <span class="text-lg font-medium text-brand-deep-ash">What types of equipment are included with kitchen rental?</span>
                        <svg class="w-6 h-6 text-brand-deep-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="faq-answer hidden p-5 border-t border-gray-200">
                        <p class="text-gray-700">Our commercial kitchens are well-equipped with standard appliances like ovens, ranges, mixers, food processors, prep tables, and multi-compartment sinks. A detailed list of equipment for each specific kitchen station is available on our kitchen rental page or upon request.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-24 bg-brand-deep-ash text-brand-text-light">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold mb-6 font-pacifico">Ready to Elevate Your Culinary Business?</h2>
            <p class="text-lg sm:text-xl mb-10 max-w-2xl mx-auto text-brand-light-blue">Don't let lack of space or equipment hold you back. Join the ProKitchen community and access the resources you need to succeed.</p>
            <a href="{{ route('kitchen-rentals.index') }}" class="bg-accent text-brand-deep-ash font-bold py-3 px-8 sm:py-4 sm:px-10 rounded-lg text-lg sm:text-xl hover:bg-accent-darker transition duration-300 transform hover:scale-105">Book Your Kitchen Now</a>
            <a href="{{ route('contact-us') }}" class="ml-4 border-2 border-accent text-accent font-bold py-3 px-8 sm:py-4 sm:px-10 rounded-lg text-lg sm:text-xl hover:bg-accent hover:text-brand-deep-ash transition duration-300 transform hover:scale-105">Ask a Question</a>
        </div>
    </section>
@endsection