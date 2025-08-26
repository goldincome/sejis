@extends('layouts.admin')
@section('header', 'Admin: '. $user->name)
@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Admin Details</h1>
        <a href="{{ route('admin.admins.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to All Admins
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">User Information</h3>
            </div>
            <div class="md:col-span-2">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Name</p>
                        <p class="text-lg text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Customer Number</p>
                        <p class="text-lg text-gray-900">{{ $user->customer_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email Address</p>
                        <p class="text-lg text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email Verified</p>
                        <p class="text-lg text-gray-900">
                            @if($user->email_verified_at)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Verified on {{ $user->email_verified_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Not Verified
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Member Since</p>
                        <p class="text-lg text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 pt-6 border-t flex justify-end">
             <a href="{{ route('admin.admins.edit', $user) }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                <i class="fas fa-pencil-alt mr-2"></i>Edit User
            </a>
        </div>
    </div>
</main>
@endsection
