<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveVisitor extends Model
{
    protected $fillable = ['session_id', 'last_activity_at'];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public static function track(string $sessionId): void
    {
        $hash = hash('sha256', $sessionId);

        static::updateOrCreate(
            ['session_id' => $hash],
            ['last_activity_at' => now()]
        );
    }

    public static function getActiveCount(int $windowMinutes = 5): int
    {
        return static::where('last_activity_at', '>=', now()->subMinutes($windowMinutes))->count();
    }

    public static function pruneExpired(int $lifetimeMinutes = 10): int
    {
        return static::where('last_activity_at', '<', now()->subMinutes($lifetimeMinutes))->delete();
    }
}
