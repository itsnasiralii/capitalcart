<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PortfolioProject extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'tech_stack',
        'image',
        'demo_url',
        'github_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'tech_stack' => 'array',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }

        return asset('images/project-placeholder.png');
    }
}
