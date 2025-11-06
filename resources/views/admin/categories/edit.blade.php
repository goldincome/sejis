@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Edit Category: {{ $category->name }}</h1>
        <a href="{{ route('admin.categories.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-500">
            <i class="fas fa-arrow-left mr-2"></i> Back to Categories
        </a>
    </div>
    
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Category Name *</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $category->name) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Product Type</label>
                    <select name="product_type" id="product_type" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Select Product Type --</option>
                        @foreach($productTypes::cases() as $productType)
                            <option value="{{ $productType->value }}" 
                                    {{ old('product_type', $category->product_type) == $productType->value ? 'selected' : '' }}>
                                {{ $productType->label() }}
                            </option>        
                        @endforeach
                    </select>
                    @error('product_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                    @if($category->hasMedia('category'))
                        <img src="{{ $category->getFirstMediaUrl('category') }}" 
                             alt="{{ $category->name }}"
                             class="h-32 rounded-md shadow-sm">
                    @else
                        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-32 h-32 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                        </div>
                    @endif
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Update Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100">
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Leave blank to keep current image</p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-500">
                <i class="fas fa-save mr-2"></i> Update Category
            </button>
        </div>
    </form>
</div>
@endsection
