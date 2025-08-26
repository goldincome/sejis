@extends('layouts.admin')
@section('header', 'View Off date')
@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Off Date Details</h1>
        <a href="{{ route('admin.off-dates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to Off Date List
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">OFF Date Information</h3>
            </div>
            <div class="md:col-span-2">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Title</p>
                        <p class="text-lg text-gray-900">{{ $off_date->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Date From</p>
                        <p class="text-lg text-gray-900">{{ $off_date->date_from->format('l, F j, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Date To</p>
                        <p class="text-lg text-gray-900">{{ $off_date->date_to->format('l, F j, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg text-gray-900">
                           @if($off_date->status)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </p>
                    </div>
                     <div>
                        <p class="text-sm font-medium text-gray-500">Created At</p>
                        <p class="text-lg text-gray-900">{{ $off_date->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 pt-6 border-t flex justify-end">
             <a href="{{ route('admin.off-dates.edit', $off_date) }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                <i class="fas fa-pencil-alt mr-2"></i>Edit Holiday
            </a>
        </div>
    </div>
</main>
@endsection
