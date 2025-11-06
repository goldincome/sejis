   <header class="bg-brand-deep-ash shadow-lg sticky top-0 z-50">
       <div class="container mx-auto px-4 sm:px-6 lg:px-8">
           <nav class="flex items-center justify-between h-20">
               <a href="{{ route('home') }}" class="font-pacifico text-brand-light-blue text-3xl md:text-4xl">Sejis</a>
               <div class="hidden md:flex space-x-3 lg:space-x-4 items-center">
                   <a href="{{ route('home') }}"
                       class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md 
                       {{ request()->routeIs('home')  ? 'text-orange-300 font-medium border-b-2 border-orange-300 pb-1' : '' }}">
                       Home
                    </a>
                   <a href="{{ route('kitchen-rentals.index') }}"
                       class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md 
                       {{ request()->routeIs('kitchen-rentals.*')  ? 'text-orange-300 font-medium border-b-2 border-orange-300 pb-1' : '' }}">
                       Kitchen Rental
                    </a>
                   <a href="{{ route('equipment-rentals.index') }}"
                       class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md 
                       {{ request()->routeIs('equipment-rentals.*')  ? 'text-orange-300 font-medium border-b-2 border-orange-300 pb-1' : '' }}">
                       Equipment
                       Rental</a>
                   <a href="{{ route('about-us') }}"
                       class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md 
                       {{ request()->routeIs('about-us')  ? 'text-orange-300 font-medium border-b-2 border-orange-300 pb-1' : '' }}">
                       About
                       Us</a>
                   <a href="{{ route('contact-us') }}"
                       class="px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md 
                       {{ request()->routeIs('contact-us')  ? 'text-orange-300 font-medium border-b-2 border-orange-300 pb-1' : '' }}">
                       Contact
                       Us</a>
                   <a href="{{ route('kitchen-rentals.index') }}"
                       class="px-4 py-2 bg-accent text-brand-deep-ash font-semibold rounded-md hover:bg-accent-darker transition duration-300">Book
                       Now</a>
                   @auth
                       <a href="{{ route('user.dashboard') }}"
                           class="px-4 py-2 bg-white text-brand-deep-ash font-semibold rounded-md hover:bg-light-blue transition duration-300 
                           {{ request()->routeIs('user.*')  ? 'text-black-300 font-medium border-b-4 border-orange-300 pb-1' : '' }}">
                           User
                           Panel</a>
                   @endauth
                   @if (Cart::count() > 0)
                       <a href="{{ route('cart.index') }}"
                           class="relative px-3 py-2 text-brand-text-light hover:text-brand-light-blue font-medium rounded-md inline-flex items-center">
                           <i class="fas fa-shopping-cart mr-1 text-lg"></i>
                           <span
                               class="absolute -top-0 -right-0 bg-orange-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center">
                               {{ Cart::count() }}
                           </span>
                       </a>
                   @endif

               </div>

               <div class="md:hidden">
                   <button id="mobile-menu-button"
                       class="text-brand-text-light hover:text-brand-light-blue focus:outline-none"><i
                           class="fas fa-bars fa-lg"></i></button>
               </div>
           </nav>
       </div>
       <div id="mobile-menu" class="md:hidden hidden bg-brand-deep-ash-lighter">
           <!-- Mobile menu links -->
           <a href="{{ route('home') }}" class="{{ request()->routeIs('home')  ? 'block px-4 py-3 text-accent bg-brand-deep-ash font-semibold' : 'block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash'}}">Home</a>
           <a href="{{ route('kitchen-rentals.index') }}"
               class="{{ request()->routeIs('kitchen-rentals.*')  ? 'block px-4 py-3 text-accent bg-brand-deep-ash font-semibold' : 'block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash'}}">
               Kitchen Rental
            </a>
           <a href="{{ route('equipment-rentals.index') }}"
               class="{{ request()->routeIs('equipment-rentals.*')  ? 'block px-4 py-3 text-accent bg-brand-deep-ash font-semibold' : 'block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash'}}">Equipment Rental</a>
           <a href="{{ route('about-us') }}" class="{{ request()->routeIs('about-us')  ? 'block px-4 py-3 text-accent bg-brand-deep-ash font-semibold' : 'block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash'}}">About Us</a>
           <a href="{{ route('contact-us') }}" class="{{ request()->routeIs('contact-us')  ? 'block px-4 py-3 text-accent bg-brand-deep-ash font-semibold' : 'block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash'}}">Contact Us</a>
           <a href="{{ route('kitchen-rentals.index') }}" class="block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">Book Now</a>
           @if (Cart::count() > 0)
               <a href="{{ route('cart.index') }}" class="relative block px-4 py-3 text-brand-text-light hover:bg-brand-deep-ash">
                   <div class="inline-block relative">
                       <i class="fas fa-shopping-cart text-lg"></i>
                       <span
                           class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center">
                           {{ Cart::count() }}
                       </span>
                   </div>
               </a>
           @endif

       </div>
   </header>
