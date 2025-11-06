 <!-- Sidebar Navigation -->
                    <aside class="lg:col-span-1 section-animate">
                        <div class="bg-white p-6 rounded-2xl shadow-2xl sticky top-28">
                            <nav class="space-y-2">
                                <a href="{{ route('user.dashboard') }}" class="group flex items-center px-4 py-3 
                                    {{ request()->routeIs('user.dashboard') ? 'text-brand-deep-ash bg-brand-light-blue rounded-lg font-bold' : ' text-gray-700 hover:bg-gray-100 rounded-lg transition-colors' }}">
                                    <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('user.orders') }}" class="group flex items-center px-4 py-3 {{ (request()->routeIs('user.orders') || request()->routeIs('user.order.details') ) ? 'text-brand-deep-ash bg-brand-light-blue rounded-lg font-bold' : 'text-gray-700 hover:bg-gray-100 rounded-lg transition-colors'}}"><i class="fas fa-calendar-alt mr-3"></i>My Bookings</a>
                                <a href="{{ route('user.profile.edit') }}" class="group flex items-center px-4 py-3 {{ (request()->routeIs('user.profile.edit') || request()->routeIs('user.order.details') ) ? 'text-brand-deep-ash bg-brand-light-blue rounded-lg font-bold' : 'text-gray-700 hover:bg-gray-100 rounded-lg transition-colors'}}"><i class="fas fa-user-circle mr-3"></i>Profile Settings</a>
                                 <a href="{{ route('user.change.password') }}" class="group flex items-center px-4 py-3 {{ (request()->routeIs('user.change.password') || request()->routeIs('user.order.details') ) ? 'text-brand-deep-ash bg-brand-light-blue rounded-lg font-bold' : 'text-gray-700 hover:bg-gray-100 rounded-lg transition-colors'}}"><i class="fas fa-user-circle mr-3"></i>Change Password</a>
                    
                    

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