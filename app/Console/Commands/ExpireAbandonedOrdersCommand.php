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
        $abandonedOrders = Order::with('items')
            ->whereIn('status', ['pending_whatsapp', 'pending'])
            ->where('payment_status', '!=', 'paid')
            ->where(function ($q) use ($cutoff) {
                $q->where('created_at', '<=', $cutoff)
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('expires_at')
                          ->where('expires_at', '<=', now());
                  });
            })
            ->get();

        $count = $abandonedOrders->count();

        if ($count === 0) {
            $this->info('No abandoned orders found to expire.');
            return Command::SUCCESS;
        }

        foreach ($abandonedOrders as $order) {
            DB::transaction(function () use ($order) {
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

                $order->update([
                    'status' => 'expired',
                    'admin_notes' => trim(($order->admin_notes ?? '') . "\nAutomatically expired after 24 hours of inactivity on " . now()->toDateTimeString()),
                ]);
            });
        }

        $this->info("Successfully expired {$count} abandoned order(s) and restored stock.");
        Log::info("Expired {$count} abandoned order(s) past 24 hours.");

        return Command::SUCCESS;
    }
}
