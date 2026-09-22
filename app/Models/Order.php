<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'is_guest', 'status', 'expires_at',
        'billing_name', 'billing_email', 'billing_phone', 'billing_address',
        'billing_city', 'billing_state', 'billing_zip', 'billing_country',
        'shipping_name', 'shipping_address', 'shipping_city', 'shipping_state',
        'shipping_zip', 'shipping_country',
        'subtotal', 'discount_amount', 'shipping_amount', 'tax_amount', 'total',
        'coupon_code', 'payment_method', 'payment_status', 'payment_reference', 'stripe_payment_intent_id',
        'customer_notes', 'admin_notes', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'is_guest'        => 'boolean',
        'expires_at'      => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
    ];

    public const STATUSES = [
        'pending_whatsapp',
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
        'expired',
        'refunded',
    ];
    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending_whatsapp' => 'warning',
            'pending'          => 'warning',
            'confirmed'        => 'info',
            'processing'       => 'primary',
            'shipped'          => 'secondary',
            'delivered'        => 'success',
            'cancelled'        => 'danger',
            'expired'          => 'dark',
            'refunded'         => 'dark',
            default            => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending_whatsapp' => 'Pending WhatsApp Confirmation',
            'pending'          => 'Pending',
            'confirmed'        => 'Confirmed',
            'processing'       => 'Processing',
            'shipped'          => 'Shipped',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
            'expired'          => 'Expired',
            'refunded'         => 'Refunded',
            default            => ucfirst($this->status),
        };
    }

    public function getMaskedPhoneAttribute(): string
    {
        return \App\Helpers\PhoneHelper::mask($this->billing_phone);
    }

    public function getMaskedNameAttribute(): string
    {
        return \App\Helpers\PhoneHelper::maskName($this->billing_name);
    }

    /**
     * Generates a cryptographically secure, collision-resistant unique order ID
     * in the format CC-XXXXXXXX (uppercase alphanumeric).
     */
    public static function generateOrderNumber(): string
    {
        do {
            $bytes = random_bytes(5); // 5 bytes = 10 hex characters
            $code = strtoupper(substr(bin2hex($bytes), 0, 8));
            $orderNumber = 'CC-' . $code;
        } while (static::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
