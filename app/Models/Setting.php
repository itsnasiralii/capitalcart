<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Helpers\PhoneHelper;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'description'];

    public static function get(string $key, $default = null)
    {
        try {
            $value = Cache::remember("setting_{$key}", 3600, function () use ($key) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : null;
            });

            if ($value === null) {
                return $default;
            }

            return $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, $value, ?string $group = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'group' => $group ?? 'general',
            ]
        );

        Cache::forget("setting_{$key}");
    }

    public static function getWhatsAppNumber(): string
    {
        return static::get('whatsapp_number', '03002922584');
    }

    public static function getWhatsAppNumberFormatted(): string
    {
        $raw = static::getWhatsAppNumber();
        return PhoneHelper::toInternational($raw);
    }

    public static function getWhatsAppUrl(string $message = ''): string
    {
        $phone = static::getWhatsAppNumberFormatted();
        $url = "https://wa.me/{$phone}";
        if (!empty($message)) {
            $url .= '?text=' . rawurlencode($message);
        }
        return $url;
    }
}
