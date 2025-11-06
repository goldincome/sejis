<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private array $cache = [];
    
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $setting = Cache::remember("setting.{$key}", 3600, function () use ($key) {
            return Setting::where('key', $key)->first();
        });
        
        $value = $setting?->value ?? $default;
        $this->cache[$key] = $value;
        
        return $value;
    }
    
    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
        
        // Clear cache
        Cache::forget("setting.{$key}");
        unset($this->cache[$key]);
    }
    
    public function getGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return Setting::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
    
    public function getPublic(): array
    {
        return Cache::remember('settings.public', 3600, function () {
            return Setting::where('is_public', true)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
    
    public function getAllByGroup(): array
    {
        return Cache::remember('settings.all_by_group', 3600, function () {
            return Setting::all()->groupBy('group')->map(function ($settings) {
                return $settings->pluck('value', 'key')->toArray();
            })->toArray();
        });
    }
    
    public function flush(): void
    {
        Cache::forget('settings.public');
        Cache::forget('settings.all_by_group');
        $this->cache = [];
        
        // Clear individual setting caches
        $keys = Setting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
        
        // Clear group caches
        $groups = Setting::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }
    }
}