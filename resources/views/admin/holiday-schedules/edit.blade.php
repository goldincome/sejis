@extends('layouts.admin')
@section('header', 'Update Off Date')
@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Off Date</h1>
        <a href="{{ route('admin.off-dates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to Off Date List
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-8">
        <form action="{{ route('admin.off-dates.update', $off_date) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $off_date->title) }}" class="w-full px-4 py-2 border @error('title') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $off_date->date_from->format('Y-m-d')) }}" class="w-full px-4 py-2 border @error('date_from') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                    @error('date_from')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $off_date->date_to->format('Y-m-d')) }}" class="w-full px-4 py-2 border @error('date_to') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                    @error('date_to')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                 <!-- Status -->
                <div class="md:col-span-2 flex items-center">
                    <input type="checkbox" name="status" id="status" value="1" {{ old('status', $off_date->status) ? 'checked' : '' }} class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                    <label for="status" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                    Update Holiday
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
