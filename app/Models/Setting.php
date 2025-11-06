<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value', 'type', 'group', 
        'is_public', 'is_encrypted', 'description'
    ];
    
    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
    ];
    
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return match($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value
        };
    }
    
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted) {
            $value = encrypt($value);
        }
        
        $this->attributes['value'] = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };
    }
    
    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget("settings.group.{$setting->group}");
            Cache::forget('settings.public');
        });
        
        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget("settings.group.{$setting->group}");
            Cache::forget('settings.public');
        });
    }
}
