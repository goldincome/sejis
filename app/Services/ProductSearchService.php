<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductSearchService
{
    /**
     * Perform advanced product search with filters
     */
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::with(['category', 'media'])
                        ->available()
                        ->advancedSearch($filters);
        
        return $query->paginate($perPage);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions(string $query, int $limit = 8): array
    {
        // Cache suggestions for 5 minutes
        $cacheKey = "search_suggestions:" . md5(strtolower($query));
        
        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            return Product::getSearchSuggestions($query, $limit);
        });
    }

    /**
     * Get popular search terms
     */
    public function getPopularSearchTerms(int $limit = 10): array
    {
        // In a real application, you'd track search terms in a separate table
        // For now, return common kitchen-related terms
        return Cache::remember('popular_search_terms', 3600, function () {
            return [
                'commercial kitchen',
                'professional oven',
                'industrial fryer',
                'kitchen equipment',
                'catering space',
                'food prep area',
                'professional mixer',
                'commercial fridge',
                'cooking station',
                'baking equipment'
            ];
        });
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(int $limit = 8): array
    {
        return Cache::remember('trending_products', 1800, function () use ($limit) {
            return Product::popular($limit)
                          ->available()
                          ->with(['category', 'media'])
                          ->get()
                          ->toArray();
        });
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 6): array
    {
        return Cache::remember('featured_products', 3600, function () use ($limit) {
            return Product::featured()
                          ->available()
                          ->with(['category', 'media'])
                          ->limit($limit)
                          ->get()
                          ->toArray();
        });
    }

    /**
     * Get filter options with counts
     */
    public function getFilterOptions(array $currentFilters = []): array
    {
        return Cache::remember('product_filter_options:' . md5(serialize($currentFilters)), 600, function () use ($currentFilters) {
            $baseQuery = Product::available();
            
            // Apply existing filters except the one we're counting for
            if (!empty($currentFilters['search'])) {
                $baseQuery->search($currentFilters['search']);
            }
            
            $options = Product::getFilterOptions();
            
            // Add counts to categories
            $options['categories'] = $options['categories']->map(function ($category) use ($baseQuery) {
                $category->product_count = (clone $baseQuery)->where('category_id', $category->id)->count();
                return $category;
            })->filter(fn($cat) => $cat->product_count > 0);
            
            // Add counts to types
            $options['types'] = collect($options['types'])->map(function ($type) use ($baseQuery) {
                $type['count'] = (clone $baseQuery)->where('type', $type['value'])->count();
                return $type;
            })->filter(fn($type) => $type['count'] > 0);
            
            // Add counts to brands
            $options['brands'] = $options['brands']->map(function ($brand) use ($baseQuery) {
                return [
                    'name' => $brand,
                    'count' => (clone $baseQuery)->where('brand', $brand)->count()
                ];
            })->filter(fn($brand) => $brand['count'] > 0);
            
            return $options;
        });
    }

    /**
     * Get related products based on category and features
     */
    public function getRelatedProducts(Product $product, int $limit = 4): array
    {
        $cacheKey = "related_products:{$product->id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($product, $limit) {
            $related = Product::where('id', '!=', $product->id)
                             ->available()
                             ->where(function ($query) use ($product) {
                                 // Same category
                                 $query->where('category_id', $product->category_id)
                                       ->orWhere('type', $product->type);
                                 
                                 // Similar features (if available)
                                 if ($product->features) {
                                     foreach ($product->features as $feature) {
                                         $query->orWhereJsonContains('features', $feature);
                                     }
                                 }
                                 
                                 // Similar brand
                                 if ($product->brand) {
                                     $query->orWhere('brand', $product->brand);
                                 }
                             })
                             ->with(['category', 'media'])
                             ->limit($limit)
                             ->get();
            
            return $related->toArray();
        });
    }

    /**
     * Perform full-text search using MySQL full-text index
     */
    public function fullTextSearch(string $query, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $searchQuery = Product::whereRaw(
            "MATCH(name, description, intro) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$query]
        )->available();
        
        // Apply additional filters
        if (!empty($filters['category_id'])) {
            $searchQuery->where('category_id', $filters['category_id']);
        }
        
        if (!empty($filters['type'])) {
            $searchQuery->where('type', $filters['type']);
        }
        
        if (!empty($filters['min_price'])) {
            $searchQuery->where('price', '>=', $filters['min_price']);
        }
        
        if (!empty($filters['max_price'])) {
            $searchQuery->where('price', '<=', $filters['max_price']);
        }
        
        return $searchQuery->with(['category', 'media'])
                          ->orderByRaw("MATCH(name, description, intro) AGAINST(? IN NATURAL LANGUAGE MODE) DESC", [$query])
                          ->paginate($perPage);
    }

    /**
     * Get search analytics data
     */
    public function getSearchAnalytics(): array
    {
        return [
            'total_products' => Product::available()->count(),
            'total_categories' => Category::whereHas('products', function ($query) {
                $query->available();
            })->count(),
            'popular_categories' => $this->getPopularCategories(),
            'price_ranges' => $this->getPriceDistribution(),
        ];
    }

    /**
     * Get popular categories with product counts
     */
    protected function getPopularCategories(int $limit = 5): array
    {
        return Category::withCount(['products' => function ($query) {
                       $query->available();
                   }])
                   ->having('products_count', '>', 0)
                   ->orderBy('products_count', 'desc')
                   ->limit($limit)
                   ->get(['id', 'name', 'slug'])
                   ->toArray();
    }

    /**
     * Get price distribution for analytics
     */
    protected function getPriceDistribution(): array
    {
        $ranges = [
            ['min' => 0, 'max' => 50, 'label' => 'Under £50'],
            ['min' => 50, 'max' => 100, 'label' => '£50 - £100'],
            ['min' => 100, 'max' => 200, 'label' => '£100 - £200'],
            ['min' => 200, 'max' => 500, 'label' => '£200 - £500'],
            ['min' => 500, 'max' => 99999, 'label' => 'Over £500']
        ];

        foreach ($ranges as &$range) {
            $range['count'] = Product::available()
                                   ->where('price', '>=', $range['min'])
                                   ->where('price', '<=', $range['max'])
                                   ->count();
        }

        return $ranges;
    }

    /**
     * Clear search-related caches
     */
    public function clearSearchCache(): void
    {
        $cacheKeys = [
            'trending_products',
            'featured_products',
            'popular_search_terms',
            'product_filter_options*'
        ];

        foreach ($cacheKeys as $key) {
            if (str_contains($key, '*')) {
                // Clear pattern-based cache keys
                Cache::flush(); // In production, use more specific cache tag clearing
            } else {
                Cache::forget($key);
            }
        }
    }

    /**
     * Log search query for analytics
     */
    public function logSearch(string $query, int $resultCount = 0, array $filters = []): void
    {
        // In a production app, you'd store this in a search_logs table
        // For now, we'll just log it
        \Log::channel('application')->info('Product search performed', [
            'query' => $query,
            'result_count' => $resultCount,
            'filters' => $filters,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Get products by availability for specific dates
     */
    public function searchByAvailability(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = []): LengthAwarePaginator
    {
        // This would need to check against existing bookings
        // For now, return available products
        $query = Product::available()
                        ->advancedSearch($filters)
                        ->whereDoesntHave('orderDetails', function ($orderQuery) use ($startDate, $endDate) {
                            $orderQuery->where('booked_date', '>=', $startDate->toDateString())
                                      ->where('booked_date', '<=', $endDate->toDateString())
                                      ->whereHas('order', function ($q) {
                                          $q->whereIn('status', ['paid', 'confirmed', 'completed']);
                                      });
                        });

        return $query->with(['category', 'media'])->paginate(12);
    }
}