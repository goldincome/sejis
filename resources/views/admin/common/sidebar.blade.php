<!-- Sidebar -->
<aside id="sidebar"
    class="admin-sidebar bg-brand-deep-ash text-white w-64 fixed inset-y-0 left-0 transform -translate-x-full lg:relative lg:translate-x-0 flex flex-col sidebar-transition z-30">
    <!-- Logo -->
    <div class="h-20 flex items-center justify-center border-b border-brand-deep-ash-lighter flex-shrink-0">
        <a href="{{ route('admin.dashboard.index') }}" class="font-pacifico text-brand-light-blue text-3xl">Sejis</a>
    </div>
    <!-- Navigation Links -->
    <nav class="flex-grow p-4 space-y-2">
        <a href="{{ route('admin.dashboard.index') }}"
            class="{{ request()->is('admin') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg font-semibold transition-colors">
            <i class="fas fa-tachometer-alt w-8 text-center text-lg"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-receipt w-8 text-center text-lg"></i>
            <span>Booking Orders</span>
        </a>
        <a href="{{ route('admin.customers.index') }}"
            class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-users w-8 text-center text-lg"></i>
            <span>Customers</span>
        </a>
        <a href="{{ route('admin.admins.index') }}"
            class="{{ request()->routeIs('admin.admins.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-users w-8 text-center text-lg"></i>
            <span>Manage Admins</span>
        </a>
        <a href="{{ route('admin.categories.index') }}"
            class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-utensils w-8 text-center text-lg"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-utensils w-8 text-center text-lg"></i>
            <span>Products</span>
        </a>
        <h1 class="justify-center text-xl font-bold flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            Settings
        </h1>
        <hr/>
        <a href="{{ route('admin.off-dates.index') }}"
            class="{{ request()->routeIs('admin.off-dates.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-cog w-8 text-center text-lg"></i>
            <span>Manage Off Dates</span>
        </a>
        <a href="{{ route('admin.opening-days.index') }}"
            class="{{ request()->routeIs('admin.opening-days.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-cog w-8 text-center text-lg"></i>
            <span>Opening Days</span>
        </a>
        <a href="{{ route('admin.settings.index') }}"
            class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg hover:bg-brand-deep-ash-lighter transition-colors">
            <i class="fas fa-cog w-8 text-center text-lg"></i>
            <span>Site Settings</span>
        </a>
    </nav>
    <!-- Logout Button -->
    <div class="p-4 border-t border-brand-deep-ash-lighter">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center px-4 py-3 rounded-lg text-red-400 hover:bg-red-500 hover:text-white transition-colors">
                <i class="fas fa-sign-out-alt w-8 text-center text-lg"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>


