<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use App\Enums\ProductTypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    public const FOLDER_NAMES = [
        'main' => 'products',
        'gallery' => 'product-gallery'
    ];
    
    public $registerMediaConversionsUsingModelInstance = true;

    protected $fillable = [
        'name', 'type', 'price', 'slug', 'intro', 'description', 'currency', 
        'is_active', 'price_per_day','category_id', 'quantity', 'features', 
        'specifications', 'capacity', 'dimensions', 'weight', 'brand',
        'model_number', 'year_manufactured'
    ];
   
    protected $casts = [
        'is_active' => 'boolean',
        'type' => ProductTypeEnum::class,
        'price' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'features' => 'array',
        'specifications' => 'array',
        'capacity' => 'integer',
        'weight' => 'decimal:2'
    ];

    protected static function boot()
    {
         parent::boot();
         static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
             }
         });
    }

    public function registerMediaCollections(): void
    {
        $this
        ->addMediaCollection(self::FOLDER_NAMES['main'])
        ->singleFile()
        ->useDisk('public')
        ->registerMediaConversions(function (Media $media) {
            $this
                ->addMediaConversion('thumb')
                ->fit(Fit::Contain, 100, 100)
                ->nonQueued();
                
        });

        $this->addMediaCollection(self::FOLDER_NAMES['gallery'])
             ->useDisk('public')
             ->registerMediaConversions(function (Media $media) {
            $this
                ->addMediaConversion('thumb')
                ->fit(Fit::Contain, 100, 100)
                ->nonQueued();
                
        });
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? true : false;
    }

    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'green' : 'red';
    }

    public function getPrimaryImageAttribute()
    {
        return $this->getFirstMedia(self::FOLDER_NAMES['main']);
    }

    public function getOtherImagesAttribute()
    {
        return $this->getMedia(self::FOLDER_NAMES['gallery']);
    }

    public function getKitchenRentalProductsAttribute()
    {
        return self::where('type', ProductTypeEnum::KITCHEN_RENTAL->value)->get();
    }
    public function getItemRentalProductAttribute()
    {
        return self::where('type', ProductTypeEnum::ITEM_RENTAL->value)->get();
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function type()
    {
        return $this->belongsTo(ProductTypeEnum::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Search products by query string
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('intro', 'LIKE', "%{$search}%")
              ->orWhere('brand', 'LIKE', "%{$search}%")
              ->orWhere('model_number', 'LIKE', "%{$search}%")
              ->orWhereJsonContains('features', $search)
              ->orWhereHas('category', function ($categoryQuery) use ($search) {
                  $categoryQuery->where('name', 'LIKE', "%{$search}%");
              });
        });
    }

    /**
     * Filter by category
     */
    public function scopeInCategory(Builder $query, $categoryId): Builder
    {
        if (empty($categoryId)) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    /**
     * Filter by product type
     */
    public function scopeOfType(Builder $query, $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Filter by price range
     */
    public function scopePriceRange(Builder $query, $minPrice = null, $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Filter by availability
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('quantity', '>', 0);
    }

    /**
     * Filter by features
     */
    public function scopeWithFeatures(Builder $query, array $features): Builder
    {
        if (empty($features)) {
            return $query;
        }

        foreach ($features as $feature) {
            $query->whereJsonContains('features', $feature);
        }

        return $query;
    }

    /**
     * Filter by capacity range
     */
    public function scopeCapacityRange(Builder $query, $minCapacity = null, $maxCapacity = null): Builder
    {
        if ($minCapacity !== null) {
            $query->where('capacity', '>=', $minCapacity);
        }

        if ($maxCapacity !== null) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query;
    }

    /**
     * Filter by brand
     */
    public function scopeByBrand(Builder $query, $brand): Builder
    {
        if (empty($brand)) {
            return $query;
        }

        return $query->where('brand', $brand);
    }

    /**
     * Sort products
     */
    public function scopeSortBy(Builder $query, $sortBy = 'name', $sortOrder = 'asc'): Builder
    {
        $allowedSorts = ['name', 'price', 'price_per_day', 'created_at', 'capacity', 'brand'];
        $allowedOrders = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }

        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'asc';
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Get popular products based on order count
     */
    public function scopePopular(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount('orderDetails')
                    ->orderBy('order_details_count', 'desc')
                    ->limit($limit);
    }

    /**
     * Get recently added products
     */
    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Get featured products
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Advanced search with all filters
     */
    public function scopeAdvancedSearch(Builder $query, array $filters): Builder
    {
        return $query->search($filters['search'] ?? null)
                    ->inCategory($filters['category_id'] ?? null)
                    ->ofType($filters['type'] ?? null)
                    ->priceRange($filters['min_price'] ?? null, $filters['max_price'] ?? null)
                    ->capacityRange($filters['min_capacity'] ?? null, $filters['max_capacity'] ?? null)
                    ->byBrand($filters['brand'] ?? null)
                    ->withFeatures($filters['features'] ?? [])
                    ->when($filters['available_only'] ?? false, function ($q) {
                        $q->available();
                    })
                    ->sortBy($filters['sort_by'] ?? 'name', $filters['sort_order'] ?? 'asc');
    }

    /**
     * Get search suggestions
     */
    public static function getSearchSuggestions(string $query, int $limit = 5): array
    {
        $products = static::search($query)
                         ->available()
                         ->limit($limit)
                         ->get(['name', 'slug']);

        $categories = \App\Models\Category::where('name', 'LIKE', "%{$query}%")
                                         ->limit($limit)
                                         ->get(['name', 'slug']);

        return [
            'products' => $products->map(fn($p) => ['name' => $p->name, 'type' => 'product', 'slug' => $p->slug]),
            'categories' => $categories->map(fn($c) => ['name' => $c->name, 'type' => 'category', 'slug' => $c->slug])
        ];
    }

    /**
     * Get filter options for search interface
     */
    public static function getFilterOptions(): array
    {
        return [
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
            'types' => collect(ProductTypeEnum::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => ucwords(str_replace('_', ' ', $type->value))
            ]),
            'brands' => static::whereNotNull('brand')
                             ->distinct()
                             ->orderBy('brand')
                             ->pluck('brand'),
            'price_ranges' => [
                ['min' => 0, 'max' => 50, 'label' => 'Under £50'],
                ['min' => 50, 'max' => 100, 'label' => '£50 - £100'],
                ['min' => 100, 'max' => 200, 'label' => '£100 - £200'],
                ['min' => 200, 'max' => 500, 'label' => '£200 - £500'],
                ['min' => 500, 'max' => null, 'label' => 'Over £500']
            ],
            'capacity_ranges' => [
                ['min' => 1, 'max' => 5, 'label' => '1-5 people'],
                ['min' => 6, 'max' => 10, 'label' => '6-10 people'],
                ['min' => 11, 'max' => 20, 'label' => '11-20 people'],
                ['min' => 21, 'max' => null, 'label' => '20+ people']
            ],
            'common_features' => [
                'Industrial Grade',
                'Commercial Kitchen',
                'Professional Equipment',
                'Fully Equipped',
                'Private Access',
                'Storage Available',
                'Parking Available',
                'WiFi Included'
            ]
        ];
    }

}
