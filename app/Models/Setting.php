<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $cacheKey = 'setting.' . $key;
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrInsert(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('setting.' . $key);
    }

    public static function getMany(array $keys): array
    {
        $rows = static::query()->whereIn('key', $keys)->pluck('value', 'key');
        $result = [];
        foreach ($keys as $k) {
            $result[$k] = $rows[$k] ?? null;
        }
        return $result;
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value === null ? null : (string) $value);
        }
    }
}
