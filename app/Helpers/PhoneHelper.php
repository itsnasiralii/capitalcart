<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Validates if a string is a valid Pakistani mobile number.
     * Valid formats:
     * - 03001234567
     * - +923001234567
     * - 923001234567
     * - 0300-1234567 / 0300 1234567
     */
    public static function isValidPakistaniNumber(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        // If it starts with 92 and is 12 digits: e.g. 923001234567
        if (preg_match('/^923[0-9]{9}$/', $digits)) {
            return true;
        }

        // If it starts with 03 and is 11 digits: e.g. 03001234567
        if (preg_match('/^03[0-9]{9}$/', $digits)) {
            return true;
        }

        // If it starts with 3 and is 10 digits: e.g. 3001234567
        if (preg_match('/^3[0-9]{9}$/', $digits)) {
            return true;
        }

        return false;
    }

    /**
     * Normalizes a Pakistani phone number to standard local format: 03XXXXXXXXX (11 digits).
     */
    public static function toLocal(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '3') && strlen($digits) === 10) {
            return '0' . $digits;
        }

        if (str_starts_with($digits, '03') && strlen($digits) === 11) {
            return $digits;
        }

        return $digits;
    }

    /**
     * Normalizes a Pakistani phone number to international format: 923XXXXXXXXX (12 digits, no plus).
     * Used for wa.me links.
     */
    public static function toInternational(?string $phone): string
    {
        if (empty($phone)) {
            return '923002922584'; // fallback default
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '03') && strlen($digits) === 11) {
            return '92' . substr($digits, 1);
        }

        if (str_starts_with($digits, '3') && strlen($digits) === 10) {
            return '92' . $digits;
        }

        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            return $digits;
        }

        return $digits ?: '923002922584';
    }

    /**
     * Masks a phone number for privacy display on public pages.
     * Example: 03001234567 -> 030*******7 (or 030*******4)
     */
    public static function mask(?string $phone): string
    {
        $normalized = static::toLocal($phone);
        if (strlen($normalized) < 7) {
            return '030*******' . substr($normalized, -1);
        }

        $prefix = substr($normalized, 0, 3); // e.g. 030
        $suffix = substr($normalized, -1);   // last digit
        return $prefix . '*******' . $suffix;
    }

    /**
     * Masks a customer name for privacy on public pages.
     * Example: Nasir Ali -> N***
     */
    public static function maskName(?string $name): string
    {
        if (empty($name)) {
            return 'Valued Customer';
        }

        $firstChar = mb_substr(trim($name), 0, 1);
        return strtoupper($firstChar) . '***';
    }
}
