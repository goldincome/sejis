@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <header class="bg-blue-700 text-white shadow-sm">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold hover:text-orange-300 transition duration-300">
                <i class="fas fa-building mr-2"></i> VMC Admin
            </a>
            <a href="{{ route('admin.virtual-addresses.index') }}" class="text-sm hover:text-orange-300 transition duration-300">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-blue-800">{{ $product->name }}</h2>
                <p class="text-gray-600 mt-1">{{ $product->intro }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 p-6">
                <div>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Main Image</h3>
                        <img src="{{ asset($product->main_product_image) }}" alt="Main Product Image" class="rounded-lg shadow-sm w-full h-64 object-cover">
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Additional Images</h3>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($product->images as $image)
                            <img src="{{ asset($image->image_path) }}" alt="Product Image" class="rounded-lg shadow-sm h-24 w-full object-cover">
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pricing & Plans</h3>
                    <div class="space-y-4">
                        @foreach($product->plans as $plan)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-medium text-gray-900">{{ $plan->name }}</h4>
                            <p class="text-orange-600 text-xl font-semibold my-2">
                                {{ $product->currency }}{{ number_format($plan->price, 2) }}
                            </p>
                            <div class="space-y-2">
                                @foreach($plan->features as $feature)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ $feature->name }}</span>
                                    <span>{{ $feature->value }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.virtual-addresses.edit', $product) }}" 
                       class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition duration-300">
                        <i class="fas fa-edit mr-2"></i> Edit Product
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection