   <header class="bg-brand-deep-ash shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-20">
                <a href="{{ route('home') }}" class="font-pacifico text-brand-light-blue text-3xl md:text-4xl">Sejis</a>
                <div class="hidden md:flex space-x-3 lg:space-x-4 items-center">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md">Home</a>
                    <a href="{{ route('kitchen-rentals.index') }}" class="px-3 py-2 text-white bg-accent/20 font-medium rounded-md">Kitchen Rental</a>
                    <a href="equipment-rental.html" class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md">Equipment Rental</a>
                    <a href="about.html" class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md">About Us</a>
                    <a href="contact.html" class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md">Contact Us</a>
                    <a href="#booking" class="px-4 py-2 bg-accent text-brand-deep-ash font-semibold rounded-md hover:bg-accent-darker transition duration-300">Book Now</a>
                    @auth
                        <a href="{{route('user.dashboard')}}" class="px-4 py-2 bg-white text-brand-deep-ash font-semibold rounded-md hover:bg-light-blue transition duration-300">User Panel</a>
                    @endauth
                    <a href="checkout.html" class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md"><i class="fas fa-shopping-cart mr-1"></i>Checkout</a>
                </div>
                
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-brand-text-light hover:text-brand-light-blue focus:outline-none"><i class="fas fa-bars fa-lg"></i></button>
                </div>
            </nav>
        </div>
        <div id="mobile-menu" class="md:hidden hidden bg-brand-deep-ash-lighter">
            <!-- Mobile menu links -->
            <a href="{{ route('home') }}" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">Home</a>
            <a href="{{ route('kitchen-rentals.index') }}" class="block px-4 py-3 text-accent bg-brand-deep-ash font-semibold">Kitchen Rental</a>
            <a href="equipment-rental.html" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">Equipment Rental</a>
            <a href="about.html" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">About Us</a>
            <a href="contact.html" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">Contact Us</a>
            <a href="#booking" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">Book Now</a>
            <a href="checkout.html" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash"><i class="fas fa-shopping-cart mr-1"></i>Checkout</a>
        </div>
    </header>

