<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(ProductSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display search results page
     */
    public function index(Request $request)
    {
        $filters = $this->getFiltersFromRequest($request);
        
        // Perform search
        $products = $this->searchService->search($filters, $request->input('per_page', 12));
        
        // Log search for analytics
        if (!empty($filters['search'])) {
            $this->searchService->logSearch(
                $filters['search'], 
                $products->total(), 
                $filters
            );
        }
        
        // Get filter options with counts
        $filterOptions = $this->searchService->getFilterOptions($filters);
        
        // Get trending and featured products for empty searches
        $trendingProducts = empty($filters['search']) 
            ? $this->searchService->getTrendingProducts(4) 
            : [];
            
        $featuredProducts = empty($filters['search']) 
            ? $this->searchService->getFeaturedProducts(6) 
            : [];
        
        return view('front.search.index', compact(
            'products', 
            'filters', 
            'filterOptions', 
            'trendingProducts', 
            'featuredProducts'
        ));
    }

    /**
     * AJAX search for autocomplete
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = $this->searchService->getSuggestions($query);
        
        return response()->json($suggestions);
    }

    /**
     * AJAX search results
     */
    public function ajaxSearch(Request $request): JsonResponse
    {
        $filters = $this->getFiltersFromRequest($request);
        $products = $this->searchService->search($filters, $request->input('per_page', 12));
        
        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem()
            ],
            'filters' => $filters
        ]);
    }

    /**
     * Full-text search
     */
    public function fullTextSearch(Request $request)
    {
        $query = $request->input('q', '');
        $filters = $this->getFiltersFromRequest($request);
        
        if (empty($query)) {
            return redirect()->route('search.index');
        }
        
        $products = $this->searchService->fullTextSearch($query, $filters);
        $filterOptions = $this->searchService->getFilterOptions($filters);
        
        return view('front.search.fulltext', compact(
            'products', 
            'query', 
            'filters', 
            'filterOptions'
        ));
    }

    /**
     * Search by availability/booking dates
     */
    public function searchByAvailability(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date'
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $filters = $this->getFiltersFromRequest($request);
        
        $products = $this->searchService->searchByAvailability($startDate, $endDate, $filters);
        $filterOptions = $this->searchService->getFilterOptions($filters);
        
        return view('front.search.availability', compact(
            'products', 
            'startDate', 
            'endDate', 
            'filters', 
            'filterOptions'
        ));
    }

    /**
     * Get popular search terms
     */
    public function popularTerms(): JsonResponse
    {
        $terms = $this->searchService->getPopularSearchTerms();
        
        return response()->json($terms);
    }

    /**
     * Get trending products
     */
    public function trending(): JsonResponse
    {
        $products = $this->searchService->getTrendingProducts();
        
        return response()->json($products);
    }

    /**
     * Get filter options
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $currentFilters = $this->getFiltersFromRequest($request);
        $options = $this->searchService->getFilterOptions($currentFilters);
        
        return response()->json($options);
    }

    /**
     * Search analytics data
     */
    public function analytics(): JsonResponse
    {
        $analytics = $this->searchService->getSearchAnalytics();
        
        return response()->json($analytics);
    }

    /**
     * Clear search cache (admin only)
     */
    public function clearCache(): JsonResponse
    {
        $this->middleware('admin');
        
        $this->searchService->clearSearchCache();
        
        return response()->json(['message' => 'Search cache cleared successfully']);
    }

    /**
     * Extract filters from request
     */
    protected function getFiltersFromRequest(Request $request): array
    {
        return [
            'search' => $request->input('q', ''),
            'category_id' => $request->input('category'),
            'type' => $request->input('type'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'min_capacity' => $request->input('min_capacity'),
            'max_capacity' => $request->input('max_capacity'),
            'brand' => $request->input('brand'),
            'features' => $request->input('features', []),
            'available_only' => $request->boolean('available_only', true),
            'sort_by' => $request->input('sort_by', 'name'),
            'sort_order' => $request->input('sort_order', 'asc')
        ];
    }
}
