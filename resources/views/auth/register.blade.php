@extends('layouts.guest')
@section('title')
    Register -VMC
@endsection
@section('description')
    Register -VMC
@endsection

@section('content')
<main class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-5xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden md:flex">
            <div class="hidden md:block md:w-1/2 auth-bg-image-register">
                <div class="flex flex-col justify-end h-full p-12 bg-black bg-opacity-40 text-white">
                    <h2 class="text-3xl font-bold leading-tight mb-3">Join VMC Today</h2>
                    <p class="text-lg text-blue-100">Unlock premium virtual office solutions, Room and Conference meeting and streamline your business operations.</p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-8 md:p-12">
                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-bold text-blue-800 mb-3">Create Your Account</h2>
                    <p class="text-gray-600 mb-8">Get started with VMC by filling out the form below.</p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="name" name="name" type="text" autocomplete="given-name" required
                               class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="John" value="{{ old('name') }}">
                        @error('name')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="email-register" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input id="email-register" name="email" type="email" autocomplete="email" required
                               class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="you@example.com" value="{{ old('email') }}">
                        @error('email')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                     <div>
                        <label for="password-register" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password-register" name="password" type="password" autocomplete="new-password" required
                               class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="Create a strong password">
                        @error('password')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                     <div>
                        <label for="confirm-password-register" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input id="confirm-password-register" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               placeholder="Confirm your password">
                        @error('password_confirmation')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                     <div class="flex items-center">
                        <input id="terms-conditions" name="terms_conditions" type="checkbox" required
                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                        <label for="terms-conditions" class="ml-2 block text-sm text-gray-900">
                            I agree to the <a href="#" class="font-medium text-orange-600 hover:text-orange-500">Terms of Service</a> and <a href="#" class="font-medium text-orange-600 hover:text-orange-500">Privacy Policy</a>.
                        </label>
                    </div>


                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i> Create Account
                        </button>
                    </div>
                </form>

                <p class="mt-8 text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-orange-600 hover:text-orange-500">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </main>
@endsection

@section('css')
    <style>
        .auth-bg-image-register {
            background-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1784&q=80'); /* Team collaboration or modern office */
            background-size: cover;
            background-position: center;
        }
    </style>
@endsection

