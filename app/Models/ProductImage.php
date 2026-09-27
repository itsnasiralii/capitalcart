<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_url', 'alt_text', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function blob(): HasOne
    {
        return $this->hasOne(ProductImageBlob::class);
    }
}
