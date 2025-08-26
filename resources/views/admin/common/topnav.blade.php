<header class="bg-white shadow-md">
    <div class="flex items-center justify-between h-20 px-8">
         <button id="menu-button" class="lg:hidden text-brand-deep-ash focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
        </button>
        <h1 class="text-2xl font-bold text-brand-deep-ash">@yield('header', 'Admin Dashboard')</h1>
        <div class="flex items-center space-x-4">
            <p class="text-gray-700">Welcome, Admin!</p>
            <img src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop"
                class="w-10 h-10 rounded-full object-cover" alt="Admin avatar">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</header>
