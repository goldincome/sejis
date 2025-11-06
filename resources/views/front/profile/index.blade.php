@extends('layouts.user')
@section('title', 'Profile - #' . $user->name)
@section('page_title', 'User Profile')
@section('page_intro', 'Profile #' . $user->name)


@section('content')
    <div class="lg:col-span-3">
        <div class="space-y-6"></div>
            <div class="bg-white p-8 rounded-2xl shadow-2xl space-y-8 section-animate">
                <!-- Edit Profile Section -->
                <div>
                    @include('front.common.error-and-message')
                    <h2 class="text-2xl font-bold text-brand-deep-ash mb-2">Edit Profile</h2>
                    <p class="text-gray-600 mb-6">Update your name and email address.</p>
                    <form action="{{ route('user.profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('patch')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="dash_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="dash_name" name="name" value="{{ $user->name }}"
                                    class="mt-1 block w-full px-4 py-3 border-gray-300 rounded-md shadow-sm focus:ring-accent focus:border-accent">
                            </div>
                            <div>
                                <label for="dash_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="dash_email" value="{{ $user->email }}"
                                    class="mt-1 block w-full px-4 py-3 border-gray-300 rounded-md shadow-sm focus:ring-accent focus:border-accent">
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit"
                                class="bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-3 px-6 rounded-lg transition duration-300">Save
                                Profile</button>
                        </div>
                    </form>
                </div>
                <!-- Change Password Link -->
                <div class="border-t pt-8">
                    <a href="user-panel-change-password.html"
                        class="font-medium text-accent hover:text-accent-darker underline">
                        Want to change your password?
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
