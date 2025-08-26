@extends('layouts.admin-guest')

@section('content')

     <main class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-5xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden md:flex">
        <!-- Left side remains unchanged -->
        <div class="hidden md:block md:w-1/2 auth-bg-image">
            <div class="flex flex-col justify-end h-full p-12 bg-black bg-opacity-40 text-white">
                <h2 class="text-3xl font-bold leading-tight mb-3">Access Your VMC Dashboard</h2>
                <p class="text-lg text-blue-100">Manage your virtual office, bookings, and mail all in one place.</p>
            </div>
        </div>

        <!-- Right side with form -->
        <div class="w-full md:w-1/2 p-8 md:p-12">
            <div class="text-center md:text-left">
                @include('admin.common.error-and-message') 
                <h2 class="text-3xl font-bold text-blue-800 mb-3">Welcome Back!</h2>
                <p class="text-gray-600 mb-8">Login to continue to your VMC account.</p>
            </div>

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Email Input with Validation -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="you@example.com"
                               value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input with Validation -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="ՕՕՕՕ">
                    </div>
                    @error('password')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox"
                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('admin.password.request') }}" class="font-medium text-orange-600 hover:text-orange-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </div>
            </form>

        </div>
    </div>
</main>

@endsection