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
