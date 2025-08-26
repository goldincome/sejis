@extends('layouts.admin')

@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Admin: {{ $user->name }}</h1>
        <a href="{{ route('admin.admins.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
             <i class="fas fa-arrow-left mr-2"></i>Back to Admins
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-8">
        <form action="{{ route('admin.admins.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
                    <input type="password" name="password" id="password" class="w-full px-4 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>
            
            <p class="text-sm text-gray-500 mt-4">Leave password fields blank to keep the current password.</p>


            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Update User
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
