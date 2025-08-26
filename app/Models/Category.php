<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'parent_id'
    ];

    protected static function boot()
    {
         parent::boot();
         static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
             }
         });
    }
    
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

     public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('category')
             ->singleFile()
             ->useDisk('public');
    }
}