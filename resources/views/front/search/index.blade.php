@extends('layouts.app')

@section('title', 'Search Products')
@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Search Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Search Products</h1>
        
        <!-- Search Form -->
        <form id="search-form" class="space-y-4">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Main Search Input -->
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           id="search-input"
                           placeholder="Search for kitchen rentals, equipment..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                           value="{{ request('search') }}">
                </div>
                
                <!-- Category Filter -->
                <div class="lg:w-48">
                    <select name="category_id" id="category-filter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Search Button -->
                <button type="submit" class="px-6 py-3 bg-brand-deep-ash text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
            
            <!-- Advanced Filters Toggle -->
            <div class="flex justify-between items-center">
                <button type="button" 
                        id="toggle-filters" 
                        class="text-brand-deep-ash hover:text-gray-700 font-medium">
                    <i class="fas fa-filter mr-2"></i>Advanced Filters
                    <i class="fas fa-chevron-down ml-1 transition-transform" id="filter-chevron"></i>
                </button>
                
                <div class="text-sm text-gray-600">
                    <span id="results-count">{{ $products->total() ?? 0 }}</span> products found
                </div>
            </div>
        </form>
        
        <!-- Advanced Filters Panel -->
        <div id="advanced-filters" class="hidden mt-6 p-4 bg-gray-50 rounded-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Product Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="kitchen_rental" {{ request('type') == 'kitchen_rental' ? 'selected' : '' }}>Kitchen Rental</option>
                        <option value="item_rental" {{ request('type') == 'item_rental' ? 'selected' : '' }}>Item Rental</option>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range (per day)</label>
                    <div class="flex gap-2">
                        <input type="number" 
                               name="min_price" 
                               placeholder="Min £" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                               value="{{ request('min_price') }}">
                        <input type="number" 
                               name="max_price" 
                               placeholder="Max £" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                               value="{{ request('max_price') }}">
                    </div>
                </div>
                
                <!-- Capacity Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity (people)</label>
                    <div class="flex gap-2">
                        <input type="number" 
                               name="min_capacity" 
                               placeholder="Min" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                               value="{{ request('min_capacity') }}">
                        <input type="number" 
                               name="max_capacity" 
                               placeholder="Max" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                               value="{{ request('max_capacity') }}">
                    </div>
                </div>
                
                <!-- Brand -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                    <input type="text" 
                           name="brand" 
                           placeholder="Enter brand name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent"
                           value="{{ request('brand') }}">
                </div>
            </div>
            
            <!-- Additional Options -->
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="available_only" 
                           value="1" 
                           class="rounded border-gray-300 text-brand-deep-ash focus:ring-brand-deep-ash"
                           {{ request('available_only') ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Available only</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="featured_only" 
                           value="1" 
                           class="rounded border-gray-300 text-brand-deep-ash focus:ring-brand-deep-ash"
                           {{ request('featured_only') ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Featured products</span>
                </label>
            </div>
            
            <!-- Sort Options -->
            <div class="flex gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select name="sort_by" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                        <option value="capacity" {{ request('sort_by') == 'capacity' ? 'selected' : '' }}>Capacity</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Newest</option>
                        <option value="popular" {{ request('sort_by') == 'popular' ? 'selected' : '' }}>Popularity</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                    <select name="sort_order" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-deep-ash focus:border-transparent">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="flex gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-brand-deep-ash text-white rounded-md hover:bg-gray-700 transition-colors">
                    Apply Filters
                </button>
                <button type="button" id="clear-filters" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                    Clear All
                </button>
            </div>
        </div>
    </div>
    
    <!-- Search Results -->
    <div id="search-results">
        @if(isset($products) && $products->count() > 0)
            @include('front.search.results', ['products' => $products])
        @elseif(isset($products))
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">No products found</h2>
                <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse our categories.</p>
                <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 bg-brand-deep-ash text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Browse All Products
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Start Your Search</h2>
                <p class="text-gray-600">Enter keywords above to find the perfect kitchen rental or equipment.</p>
            </div>
        @endif
    </div>
    
    <!-- Popular Searches -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Popular Searches</h3>
        <div class="flex flex-wrap gap-2">
            @foreach(['Commercial Kitchen', 'Food Truck', 'Catering Equipment', 'Professional Oven', 'Industrial Mixer', 'Refrigeration'] as $term)
                <a href="{{ route('search.index', ['search' => $term]) }}" 
                   class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-brand-light-blue hover:text-brand-deep-ash transition-colors">
                    {{ $term }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const toggleFilters = document.getElementById('toggle-filters');
    const advancedFilters = document.getElementById('advanced-filters');
    const filterChevron = document.getElementById('filter-chevron');
    const clearFilters = document.getElementById('clear-filters');
    const searchResults = document.getElementById('search-results');
    
    // Toggle advanced filters
    toggleFilters.addEventListener('click', function() {
        advancedFilters.classList.toggle('hidden');
        filterChevron.classList.toggle('rotate-180');
    });
    
    // Clear all filters
    clearFilters.addEventListener('click', function() {
        searchForm.reset();
        window.location.href = '{{ route("search.index") }}';
    });
    
    // AJAX search functionality
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 300);
    });
    
    // Form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    function performSearch() {
        const formData = new FormData(searchForm);
        const searchParams = new URLSearchParams(formData);
        
        // Update URL without page reload
        const newUrl = '{{ route("search.index") }}?' + searchParams.toString();
        window.history.pushState({}, '', newUrl);
        
        // Perform AJAX search
        fetch('{{ route("search.ajax") }}?' + searchParams.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            searchResults.innerHTML = data.html;
            document.getElementById('results-count').textContent = data.total;
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    }
    
    // Auto-suggestions for search input
    let suggestionTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(suggestionTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            suggestionTimeout = setTimeout(() => {
                fetch('{{ route("search.suggestions") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(suggestions => {
                    showSuggestions(suggestions);
                });
            }, 200);
        } else {
            hideSuggestions();
        }
    });
    
    function showSuggestions(suggestions) {
        // Implementation for showing search suggestions
        // This would create a dropdown with suggested search terms
    }
    
    function hideSuggestions() {
        // Implementation for hiding search suggestions
    }
});
</script>
@endpush
@endsection