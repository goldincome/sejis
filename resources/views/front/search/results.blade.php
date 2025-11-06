<!-- Search Results Grid -->
<div class="bg-white rounded-lg shadow-lg p-6">
    <!-- Results Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            Search Results ({{ $products->total() }} {{ Str::plural('product', $products->total()) }})
        </h2>
        
        <!-- View Toggle -->
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">View:</span>
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button id="grid-view" class="px-3 py-1 rounded-md bg-white shadow-sm text-brand-deep-ash">
                    <i class="fas fa-th-large"></i>
                </button>
                <button id="list-view" class="px-3 py-1 rounded-md text-gray-600 hover:text-brand-deep-ash">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($products as $product)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <!-- Product Image -->
                <div class="relative aspect-w-16 aspect-h-10 bg-gray-100">
                    @if($product->primary_image)
                        <img src="{{ $product->primary_image->getUrl() }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-3xl"></i>
                        </div>
                    @endif
                    
                    <!-- Product Badges -->
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        @if($product->is_featured)
                            <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">Featured</span>
                        @endif
                        @if($product->type === 'kitchen_rental')
                            <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded">Kitchen</span>
                        @else
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Equipment</span>
                        @endif
                    </div>
                    
                    <!-- Quick View Button -->
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <button class="bg-white text-brand-deep-ash px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                            Quick View
                        </button>
                    </div>
                </div>
                
                <!-- Product Details -->
                <div class="p-4">
                    <!-- Product Name & Category -->
                    <div class="mb-2">
                        <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-1">
                            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-brand-deep-ash transition-colors">
                                {{ $product->name }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600">{{ $product->category->name ?? 'Uncategorized' }}</p>
                    </div>
                    
                    <!-- Product Description -->
                    @if($product->intro)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $product->intro }}</p>
                    @endif
                    
                    <!-- Product Specifications -->
                    <div class="mb-3 space-y-1">
                        @if($product->capacity)
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-users w-4 mr-2"></i>
                                <span>Capacity: {{ $product->capacity }} people</span>
                            </div>
                        @endif
                        @if($product->dimensions)
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-ruler w-4 mr-2"></i>
                                <span>Size: {{ $product->dimensions }}</span>
                            </div>
                        @endif
                        @if($product->brand)
                            <div class="flex items-center text-xs text-gray-600">
                                <i class="fas fa-tag w-4 mr-2"></i>
                                <span>Brand: {{ $product->brand }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Features -->
                    @if($product->features && is_array($product->features))
                        <div class="mb-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($product->features, 0, 3) as $feature)
                                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">{{ $feature }}</span>
                                @endforeach
                                @if(count($product->features) > 3)
                                    <span class="text-xs text-gray-500">+{{ count($product->features) - 3 }} more</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Pricing -->
                    <div class="mb-4">
                        @if($product->price_per_day)
                            <div class="text-lg font-bold text-brand-deep-ash">
                                £{{ number_format($product->price_per_day, 2) }}
                                <span class="text-sm font-normal text-gray-600">per day</span>
                            </div>
                        @endif
                        @if($product->price && $product->price !== $product->price_per_day)
                            <div class="text-sm text-gray-600">
                                Total: £{{ number_format($product->price, 2) }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="flex-1 bg-brand-deep-ash text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                            View Details
                        </a>
                        <button class="px-4 py-2 border border-brand-deep-ash text-brand-deep-ash rounded-lg hover:bg-brand-deep-ash hover:text-white transition-colors"
                                onclick="addToWishlist({{ $product->id }})">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- List View (Hidden by default) -->
    <div id="products-list" class="hidden space-y-4">
        @foreach($products as $product)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="flex">
                    <!-- Product Image -->
                    <div class="w-48 h-32 bg-gray-100 flex-shrink-0">
                        @if($product->primary_image)
                            <img src="{{ $product->primary_image->getUrl() }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-2xl"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Product Details -->
                    <div class="flex-1 p-4">
                        <div class="flex justify-between">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800 mb-1">
                                    <a href="{{ route('products.show', $product->slug) }}" class="hover:text-brand-deep-ash transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                
                                @if($product->intro)
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $product->intro }}</p>
                                @endif
                                
                                <!-- Specifications in List View -->
                                <div class="flex flex-wrap gap-4 text-xs text-gray-600 mb-2">
                                    @if($product->capacity)
                                        <span><i class="fas fa-users mr-1"></i>{{ $product->capacity }} people</span>
                                    @endif
                                    @if($product->dimensions)
                                        <span><i class="fas fa-ruler mr-1"></i>{{ $product->dimensions }}</span>
                                    @endif
                                    @if($product->brand)
                                        <span><i class="fas fa-tag mr-1"></i>{{ $product->brand }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Price and Actions -->
                            <div class="text-right">
                                @if($product->price_per_day)
                                    <div class="text-lg font-bold text-brand-deep-ash mb-2">
                                        £{{ number_format($product->price_per_day, 2) }}
                                        <span class="text-sm font-normal text-gray-600 block">per day</span>
                                    </div>
                                @endif
                                
                                <div class="flex gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}" 
                                       class="bg-brand-deep-ash text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                                        View Details
                                    </a>
                                    <button class="px-3 py-2 border border-brand-deep-ash text-brand-deep-ash rounded-lg hover:bg-brand-deep-ash hover:text-white transition-colors"
                                            onclick="addToWishlist({{ $product->id }})">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- No Results Message -->
    @if($products->count() === 0)
        <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No products found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse our categories.</p>
            <a href="{{ route('products.index') }}" 
               class="inline-block bg-brand-deep-ash text-white py-2 px-6 rounded-lg font-medium hover:bg-gray-700 transition-colors">
                Browse All Products
            </a>
        </div>
    @endif
    
    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('grid-view');
    const listViewBtn = document.getElementById('list-view');
    const productsGrid = document.getElementById('products-grid');
    const productsList = document.getElementById('products-list');
    
    // View toggle functionality
    gridViewBtn?.addEventListener('click', function() {
        productsGrid.classList.remove('hidden');
        productsList.classList.add('hidden');
        gridViewBtn.classList.add('bg-white', 'shadow-sm', 'text-brand-deep-ash');
        gridViewBtn.classList.remove('text-gray-600');
        listViewBtn.classList.remove('bg-white', 'shadow-sm', 'text-brand-deep-ash');
        listViewBtn.classList.add('text-gray-600');
    });
    
    listViewBtn?.addEventListener('click', function() {
        productsList.classList.remove('hidden');
        productsGrid.classList.add('hidden');
        listViewBtn.classList.add('bg-white', 'shadow-sm', 'text-brand-deep-ash');
        listViewBtn.classList.remove('text-gray-600');
        gridViewBtn.classList.remove('bg-white', 'shadow-sm', 'text-brand-deep-ash');
        gridViewBtn.classList.add('text-gray-600');
    });
});

// Wishlist functionality
function addToWishlist(productId) {
    // Implementation for adding products to wishlist
    fetch('/wishlist/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            console.log('Added to wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush