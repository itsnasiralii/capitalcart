<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasUuids;

    protected $fillable = ['sha256', 'mime_type', 'byte_size', 'width', 'height', 'content_base64'];

    protected $hidden = ['content_base64'];

    public function getUrlAttribute(): string
    {
        return route('media.show', ['media' => $this->id], false);
    }
}
