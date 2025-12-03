<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class AppSettings
{
    private const CACHE_PREFIX = 'app_settings:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = AppSetting::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value (upsert).
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $description = null): void
    {
        $storedValue = match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };

        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };

        $data = [
            'value' => $storedValue,
            'type' => $type,
        ];

        if ($group !== null) {
            $data['group'] = $group;
        }

        if ($description !== null) {
            $data['description'] = $description;
        }

        AppSetting::updateOrCreate(['key' => $key], $data);

        // Clear cache for this key
        self::forget($key);
    }

    /**
     * Clear cached value for a specific key.
     */
    public static function forget(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear all cached settings.
     */
    public static function flush(): void
    {
        $settings = AppSetting::all();

        foreach ($settings as $setting) {
            self::forget($setting->key);
        }
    }

    /**
     * Get all settings, optionally filtered by group.
     */
    public static function all(?string $group = null): array
    {
        $query = AppSetting::query();

        if ($group !== null) {
            $query->where('group', $group);
        }

        $settings = $query->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getTypedValue();
        }

        return $result;
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        return AppSetting::where('key', $key)->exists();
    }

    /**
     * Delete a setting by key.
     */
    public static function delete(string $key): bool
    {
        self::forget($key);

        return AppSetting::where('key', $key)->delete() > 0;
    }
}
