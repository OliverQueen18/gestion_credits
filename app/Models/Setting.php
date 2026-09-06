<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('settings.all', 60, function () {
            return static::query()->pluck('value', 'key');
        });

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        Cache::forget('settings.all');
    }

    public static function allowsNegativeBalance(): bool
    {
        return static::getValue('allow_negative_balance', '0') === '1';
    }

    public static function brandLogoPath(): string
    {
        return public_path('images/logo-gestion-credit.png');
    }

    public static function logoFullPath(): ?string
    {
        $path = (string) static::getValue('logo_path', '');
        if ($path !== '') {
            $full = storage_path('app/public/'.$path);
            if (is_file($full)) {
                return $full;
            }
        }

        $brand = static::brandLogoPath();

        return is_file($brand) ? $brand : null;
    }
}
