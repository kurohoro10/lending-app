<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_secret'];

    protected $casts = [
        'is_secret' => 'boolean',
    ];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set a setting value, creating or updating as needed.
     */
    public static function set(string $key, mixed $value, bool $isSecret = false): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'is_secret' => $isSecret]
        );

        Cache::forget("setting:{$key}");
    }

    /**
     * Set multiple settings at once.
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Cache::forget("setting:{$key}");
        }

        foreach ($settings as $key => ['value' => $value, 'is_secret' => $isSecret]) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'is_secret' => $isSecret]
            );
        }
    }

    /**
     * Get all settings for a group prefix (e.g. 'twilio').
     */
    public static function getGroup(string $prefix): array
    {
        return static::where('key', 'like', "{$prefix}_%")
            ->pluck('value', 'key')
            ->toArray();
    }
}
