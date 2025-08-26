<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use App\Enums\ProductTypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    public const FOLDER_NAMES = [
        'main' => 'products',
        'gallery' => 'product-gallery'
    ];
    public $registerMediaConversionsUsingModelInstance = true;

    protected $fillable = [
        'name', 'type', 'price', 'slug', 'intro', 'description', 'currency', 
        'is_active', 'price_per_day','category_id', 'quantity'
    ];
   
    protected $casts = ['is_active' => 'boolean',
        'type' => ProductTypeEnum::class,
        'price' => 'decimal:2',
        'is_active' => 'boolean',
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

}
