<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class RecentOrders extends Component
{
    public function render()
    {
        $enabled = (bool) Setting::get('show_recent_orders', 1);
        if (!$enabled) {
            return view('livewire.recent-orders', ['orders' => collect()]);
        }

        // Cache anonymized orders for 60 seconds
        $orders = Cache::remember('recent_orders_public', 60, function () {
            return Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered', 'pending_whatsapp', 'pending'])
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'masked_name'  => $order->masked_name,
                        'masked_phone' => $order->masked_phone,
                        'time_ago'     => $order->created_at->diffForHumans(),
                        'city'         => $order->shipping_city ?: $order->billing_city ?: 'Islamabad',
                    ];
                });
        });

        return view('livewire.recent-orders', [
            'orders' => $orders,
        ]);
    }
}
