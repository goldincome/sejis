@extends('layouts.admin')

@section('content')
    <!-- Page Content -->
    <main class="flex-1 p-4 sm:p-8 bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
            <form action="#" method="POST" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="name" name="name" value="John Doe" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" name="email" value="johndoe@example.com" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent">
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                    <p class="text-sm text-gray-500 mt-1">Leave these fields blank to keep the current password.</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" id="password" name="password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New
                        Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-accent focus:border-accent">
                </div>

                <div class="border-t pt-6 flex justify-end space-x-4">
                    <a href="admin-user-list.html"
                        class="bg-gray-200 hover:bg-gray-300 text-brand-deep-ash font-bold py-2 px-6 rounded-lg transition duration-300">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-accent hover:bg-accent-darker text-brand-deep-ash font-bold py-2 px-6 rounded-lg transition duration-300">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection
