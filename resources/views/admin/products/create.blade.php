@extends('layouts.admin')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <header class="bg-blue-700 text-white shadow-sm">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <a href="#" class="text-2xl font-bold hover:text-orange-300 transition duration-300">
                    <i class="fas fa-building mr-2"></i> VMC Admin
                </a>
                <a href="{{ route('admin.products.index') }}" class="text-sm hover:text-orange-300 transition duration-300">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Products
                </a>
            </nav>
        </header>

        <main class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-3xl bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-blue-800">Create New Product</h2>
                </div>

                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.store') }}"
                    class="p-8 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mb-4">
                            <label for="product_type" class="block text-sm font-medium text-gray-700 mb-2">Product Rental
                                Category</label>
                            <select name="category_id" id="category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('category_id') border-red-500 @enderror"
                                autocomplete="off">
                                <option value="">Select Product Type</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Rental Name</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 @error('name') border-red-500 @enderror"
                                value="{{ old('name') }}">
                            @error('name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price per hr</label>
                            <div class="relative">
                                <input type="number" step="0.01" id="price" name="price" required
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 @error('price') border-red-500 @enderror"
                                    value="{{ old('price') }}">
                            </div>
                            @error('price')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_per_day" class="block text-sm font-medium text-gray-700 mb-2">Price per
                                day</label>
                            <div class="relative">
                                <input type="number" step="0.01" id="price_per_day" name="price_per_day"
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 @error('price_per_day') border-red-500 @enderror"
                                    value="{{ old('price_per_day') }}">
                            </div>
                            @error('price_per_day')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="intro" class="block text-sm font-medium text-gray-700 mb-2">Short Intro</label>
                            <textarea id="intro" name="intro" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 @error('intro') border-red-500 @enderror">{{ old('intro') }}</textarea>
                            @error('intro')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Modify the main image section in your Blade file -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Main Product Image</label>
                            <div class="mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 @error('main_product_image') border-red-500 @else border-gray-300 @enderror border-dashed rounded-lg"
                                id="main-image-dropzone">
                                <div class="space-y-1 text-center" id="main-image-content">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex justify-center text-sm text-gray-600">
                                        <label for="main_product_image"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                            <span>Upload main image</span>
                                            <input id="main_product_image" name="main_product_image" type="file"
                                                class="sr-only" required>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                </div>
                                <div id="main-image-preview" class="hidden relative w-full h-full">
                                    <!-- Preview will be inserted here -->
                                </div>
                            </div>
                            @error('main_product_image')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Images</label>
                            <div class="mt-1 relative border-2 @error('additional_images.*') border-red-500 @else @error('additional_images') border-red-500 @else border-gray-300 @enderror @enderror border-dashed rounded-lg px-6 pt-5 pb-6"
                                id="dropzone">
                                <div class="flex flex-wrap gap-4" id="preview-container"></div>
                                <div class="text-center" id="dropzone-content">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex justify-center text-sm text-gray-600">
                                        <label
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                            <span>Drag files or click to upload</span>
                                            <input id="additional_images" name="additional_images[]" type="file"
                                                multiple class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 5 files, 2MB each</p>
                                </div>
                            </div>
                            @error('additional_images') {{-- General error for the array --}}
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @foreach ($errors->get('additional_images.*') as $message)
                                {{-- Specific errors for each file in the array --}}
                                <p class="mt-1 text-xs text-red-600">{{ $message[0] }}</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="px-4 py-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Enabled</label>
                            <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="1" {{ old('is_active') ? 'selected' : '' }} >Yes</option>
                                <option value="0" {{ !old('is_active') ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-500">
                            Create Product
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('additional_images');
            const previewContainer = document.getElementById('preview-container');
            const dropzoneContent = document.getElementById('dropzone-content');

            // Handle drag and drop events
            ['dragover', 'dragenter'].forEach(event => {
                dropzone.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('border-orange-500', 'bg-orange-50');
                });
            });

            ['dragleave', 'dragend'].forEach(event => {
                dropzone.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('border-orange-500', 'bg-orange-50');
                });
            });

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-orange-500', 'bg-orange-50');
                const files = e.dataTransfer.files;
                fileInput.files = files; // Assign dropped files to the input
                handleFiles(files);
            });

            // Handle file input change
            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });

            function handleFiles(files) {
                previewContainer.innerHTML = ''; // Clear previous previews
                if (files.length > 0) {
                    dropzoneContent.classList.add('hidden');
                } else {
                    dropzoneContent.classList.remove('hidden');
                }

                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewWrapper = document.createElement('div');
                        previewWrapper.className =
                            'relative w-24 h-24 group'; // Added group for hover effect on button

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-full object-cover rounded-lg shadow-md';
                        previewWrapper.appendChild(img);

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className =
                            'absolute -top-2 -right-2 text-red-600 bg-white rounded-full p-0.5 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity'; // Initially hidden, shown on hover
                        removeButton.innerHTML =
                            '<i class="fas fa-times-circle text-lg"></i>'; // Larger icon
                        removeButton.onclick = () => removeImage(index);
                        previewWrapper.appendChild(removeButton);

                        previewContainer.appendChild(previewWrapper);
                    };
                    reader.readAsDataURL(file);
                });
            }

            window.removeImage = (indexToRemove) => {
                const dt = new DataTransfer();
                const currentFiles = Array.from(fileInput.files);

                currentFiles.forEach((file, index) => {
                    if (index !== indexToRemove) {
                        dt.items.add(file);
                    }
                });

                fileInput.files = dt.files; // Update the FileList object
                handleFiles(fileInput.files); // Re-render previews
            }


            // Add this to your existing JavaScript
            const mainImageDropzone = document.getElementById('main-image-dropzone');
            const mainImageInput = document.getElementById('main_product_image');
            const mainImagePreview = document.getElementById('main-image-preview');
            const mainImageContent = document.getElementById('main-image-content');

            mainImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        mainImageContent.classList.add('hidden');
                        mainImagePreview.classList.remove('hidden');
                        mainImagePreview.innerHTML = `
                        <div class="relative w-32 h-32 mx-auto">
                            <img src="${e.target.result}" class="w-full h-full object-cover rounded-lg shadow-md">
                            <button type="button" class="absolute -top-2 -right-2 text-red-600 bg-white rounded-full p-0.5 shadow-sm" onclick="removeMainImage()">
                                <i class="fas fa-times-circle text-lg"></i>
                            </button>
                        </div>
                    `;
                    };
                    reader.readAsDataURL(file);
                }
            });

            window.removeMainImage = () => {
                mainImageInput.value = '';
                mainImagePreview.classList.add('hidden');
                mainImageContent.classList.remove('hidden');
                mainImagePreview.innerHTML = '';
            };
        });
    </script>
@endpush
