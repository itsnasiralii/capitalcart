<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImageBlob extends Model
{
    protected $fillable = [
        'product_image_id',
        'image_data',
        'mime_type',
        'file_size',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'product_image_id');
    }
}
