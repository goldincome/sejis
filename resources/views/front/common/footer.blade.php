<footer class="bg-gray-900 text-gray-400 py-12 sm:py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="font-pacifico text-brand-light-blue text-2xl mb-4">Sejis</h3>
                    <p class="mb-3 text-sm">Empowering food entrepreneurs with professional kitchen spaces and equipment rentals.</p>
                    <p class="text-sm"><i class="fas fa-map-marker-alt mr-2 text-brand-light-blue"></i>123 Culinary Avenue, Foodie City, FC 54321</p>
                    <p class="text-sm"><i class="fas fa-phone mr-2 text-brand-light-blue"></i>(555) 123-4567</p>
                    <p class="text-sm"><i class="fas fa-envelope mr-2 text-brand-light-blue"></i>info@Sejis.co.uk</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about-us') }}" class="hover:text-brand-light-blue transition-colors">About Us</a></li>
                        <li><a href="{{ route('kitchen-rentals.index') }}" class="hover:text-brand-light-blue transition-colors">Kitchen Rental</a></li>
                        <li><a href="{{ route('equipment-rentals.index') }}" class="hover:text-brand-light-blue transition-colors">Equipment Rental</a></li>
                        <li><a href="#" class="hover:text-brand-light-blue transition-colors">FAQ</a></li> 
                        <li><a href="{{ route('contact-us') }}" class="hover:text-brand-light-blue transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('terms-of-service') }}" class="hover:text-brand-light-blue transition-colors">Terms of Service</a></li>
                        <li><a href="{{ route('privacy-policy') }}" class="hover:text-brand-light-blue transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('booking-policy') }}" class="hover:text-brand-light-blue transition-colors">Booking Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Connect With Us</h4>
                    <p class="mb-3 text-sm">Follow us for updates, tips, and community news.</p>
                    <div class="flex space-x-4">
                        <a href="#" aria-label="Facebook" class="text-gray-400 hover:text-brand-light-blue text-xl transition-colors"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram" class="text-gray-400 hover:text-brand-light-blue text-xl transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter" class="text-gray-400 hover:text-brand-light-blue text-xl transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn" class="text-gray-400 hover:text-brand-light-blue text-xl transition-colors"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-8 text-center text-sm">
                <p>&copy; <span id="currentYear"></span> Sejis Rentals. All Rights Reserved.</p>
            </div>
        </div>
    </footer>