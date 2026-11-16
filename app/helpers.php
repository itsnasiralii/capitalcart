<?php

use App\Models\Setting;
use App\Helpers\PhoneHelper;

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('whatsapp_url')) {
    function whatsapp_url(string $message = ''): string
    {
        return Setting::getWhatsAppUrl($message);
    }
}

if (!function_exists('mask_phone')) {
    function mask_phone(?string $phone): string
    {
        return PhoneHelper::mask($phone);
    }
}

if (!function_exists('mask_name')) {
    function mask_name(?string $name): string
    {
        return PhoneHelper::maskName($name);
    }
}

function is_public_web_url(?string $url): bool
{
    return $url !== null && filter_var($url, FILTER_VALIDATE_URL) !== false
        && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function is_store_link(?string $url): bool
{
    return is_public_web_url($url) || ($url !== null && preg_match('#^/(?!/)[^\\\\\s]*$#D', $url) === 1);
}

function csv_cell(mixed $value): mixed
{
    if (is_string($value) && preg_match('/^(?:[\t\r\n]|\s*[=+@-])/u', $value)) {
        return "'".$value;
    }
    return $value;
}
