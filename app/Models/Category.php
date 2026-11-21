<?php

namespace App\Models;

use App\Helpers\PhoneHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'image_url', 'whatsapp_number', 'parent_id', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getWhatsAppNumber(): string
    {
        return $this->whatsapp_number ?: Setting::getWhatsAppNumber();
    }

    public function getWhatsAppUrl(string $message = ''): string
    {
        $phone = PhoneHelper::toInternational($this->getWhatsAppNumber());
        $url = "https://wa.me/{$phone}";

        return $message !== '' ? $url . '?text=' . rawurlencode($message) : $url;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getImageUrlAttribute(?string $value): string
    {
        if (!empty($value)) {
            return $value;
        }

        $firstProduct = $this->products()->where('is_active', true)->with('images')->first();
        if ($firstProduct && $firstProduct->primary_image_url) {
            return $firstProduct->primary_image_url;
        }

        return "https://picsum.photos/seed/cat-{$this->id}/400/300";
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->where('is_active', true)->count();
    }
}
