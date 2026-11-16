<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireAbandonedOrdersCommand extends Command
{
    protected $signature = 'orders:expire-abandoned';
    protected $description = 'Expire pending temporary guest WhatsApp orders older than the configured expiration timeframe without affecting completed or confirmed orders';

    public function handle(): int
    {
        $hours = (int) Setting::get('order_expiration_hours', 24);
        $cutoff = now()->subHours($hours);

        // Find orders in pending_whatsapp or pending status that are older than 24h and unpaid
        $abandonedOrders = Order::query()
            ->whereIn('status', ['pending_whatsapp', 'pending'])
            ->where('payment_status', '!=', 'paid')
            ->where(function ($q) use ($cutoff) {
                $q->where('created_at', '<=', $cutoff)
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('expires_at')
                          ->where('expires_at', '<=', now());
                  });
            });

        $count = 0;
        foreach ($abandonedOrders->lazyById(100) as $candidate) {
            $expired = DB::transaction(function () use ($candidate, $cutoff) {
                $order = Order::lockForUpdate()->find($candidate->id);
                if (!$order || !in_array($order->status, ['pending', 'pending_whatsapp'], true)
                    || $order->payment_status === 'paid'
                    || ($order->created_at->gt($cutoff) && !$order->expires_at?->isPast())) {
                    return false;
                }
                if ($order->payment_method === 'stripe') {
                    try {
                        if (!app(\App\Services\StripeCheckout::class)->canRelease($order)) {
                            return false;
                        }
                    } catch (\Stripe\Exception\ApiErrorException $exception) {
                        Log::warning('Deferred card reservation expiry', ['order_id' => $order->id]);
                        return false;
                    }
                }
                // Return stock back to inventory if stock was reserved
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)
                            ->increment('stock_quantity', $item->quantity);
                    }
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)
                            ->increment('stock_quantity', $item->quantity);
                    }
                }

                if ($order->coupon_code) {
                    \App\Models\Coupon::where('code', $order->coupon_code)->where('used_count', '>', 0)->decrement('used_count');
                }
                $order->update([
                    'status' => 'expired',
                    'admin_notes' => trim(($order->admin_notes ?? '') . "\nAutomatically expired after 24 hours of inactivity on " . now()->toDateTimeString()),
                ]);
                return true;
            });
            $count += (int) $expired;
        }

        if ($count === 0) {
            $this->info('No abandoned orders found to expire.');
            return Command::SUCCESS;
        }

        $this->info("Successfully expired {$count} abandoned order(s) and restored stock.");
        Log::info("Expired {$count} abandoned order(s) past 24 hours.");

        return Command::SUCCESS;
    }
}
