@extends('layouts.user')
@section('title', 'Change Password' )
@section('page_title', 'Change Passpord')
@section('page_intro', 'Change Password' )


@section('content')
    <div class="lg:col-span-3">
        <div class="space-y-6"></div>
            <div class="bg-white p-8 rounded-2xl shadow-2xl space-y-8 section-animate">
                            <!-- Change Password Section -->
                            <div>
                                @include('front.common.error-and-message')
                                <h2 class="text-2xl font-bold text-brand-deep-ash mb-2">Change Password</h2>
                                <p class="text-gray-600 mb-6">Choose a strong, new password to keep your account secure.</p>
                                <form action="{{ route('password.update') }}" method="POST" class="space-y-6 max-w-lg">
                                    @csrf
                                    @method('put')
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" required class="mt-1 block w-full px-4 py-3 border-gray-300 rounded-md shadow-sm focus:ring-accent focus:border-accent">
                                    </div>
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                        <input type="password" id="new_password" name="password" required class="mt-1 block w-full px-4 py-3 border-gray-300 rounded-md shadow-sm focus:ring-accent focus:border-accent">
                                    </div>
                                    <div>
                                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" required class="mt-1 block w-full px-4 py-3 border-gray-300 rounded-md shadow-sm focus:ring-accent focus:border-accent">
                                    </div>
                                    <div class="pt-2">
                                         <button type="submit" class="bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-6 rounded-lg transition duration-300">Update Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
        </div>
    </div>
@endsection
