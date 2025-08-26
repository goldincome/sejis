 <!-- Sidebar Navigation -->
                    <aside class="lg:col-span-1 section-animate">
                        <div class="bg-white p-6 rounded-2xl shadow-2xl sticky top-28">
                            <nav class="space-y-2">
                                <a href="#" class="group flex items-center px-4 py-3 text-brand-deep-ash bg-brand-light-blue rounded-lg font-bold"><i class="fas fa-tachometer-alt mr-3 text-lg"></i>Dashboard</a>
                                <a href="#" class="group flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"><i class="fas fa-calendar-alt mr-3 text-gray-500 group-hover:text-brand-deep-ash"></i>My Bookings</a>
                                <a href="#" class="group flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"><i class="fas fa-user-circle mr-3 text-gray-500 group-hover:text-brand-deep-ash"></i>Profile Settings</a>
                                <a href="#" class="group flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"><i class="fas fa-receipt mr-3 text-gray-500 group-hover:text-brand-deep-ash"></i>Order History</a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="group flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
                                        <i class="fas fa-sign-out-alt mr-3 text-gray-500 group-hover:text-red-700"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </nav>
                        </div>
                    </aside>