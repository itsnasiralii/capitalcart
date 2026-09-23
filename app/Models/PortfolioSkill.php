<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PortfolioSkill extends Model
{
    protected $fillable = [
        'category',
        'skills',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'skills'     => 'array',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id', 'asc');
    }
}
