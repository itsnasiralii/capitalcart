<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $paymentMethod = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $sort = 'newest';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }
    public function updatedPaymentMethod(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $order = Order::findOrFail($orderId);
        $order->status = $status;
        if ($status === 'shipped') $order->shipped_at = now();
        if ($status === 'delivered') $order->delivered_at = now();
        $order->save();

        $this->dispatch('show-toast', message: "Order #{$order->order_number} marked as {$order->status_label}.", type: 'success');
    }

    public function exportCsv(): StreamedResponse
    {
        $query = $this->buildQuery();
        $orders = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="capitalcart_orders_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Customer Phone',
                'Customer Email',
                'Shipping Address',
                'City',
                'Subtotal (PKR)',
                'Delivery Fee (PKR)',
                'Discount (PKR)',
                'Total (PKR)',
                'Payment Method',
                'Order Status',
                'Created Date',
                'Expiry Date',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->billing_name,
                    $order->billing_phone,
                    $order->billing_email,
                    $order->shipping_address,
                    $order->shipping_city,
                    $order->subtotal,
                    $order->shipping_amount,
                    $order->discount_amount,
                    $order->total,
                    $order->payment_method,
                    $order->status,
                    $order->created_at->toDateTimeString(),
                    $order->expires_at?->toDateTimeString() ?? 'N/A',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function buildQuery()
    {
        $query = Order::with('items');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', "%{$this->search}%")
                  ->orWhere('billing_name', 'like', "%{$this->search}%")
                  ->orWhere('billing_phone', 'like', "%{$this->search}%")
                  ->orWhere('billing_email', 'like', "%{$this->search}%");
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return match ($this->sort) {
            'total_desc' => $query->orderBy('total', 'desc'),
            'total_asc'  => $query->orderBy('total', 'asc'),
            'oldest'     => $query->oldest(),
            default      => $query->latest(),
        };
    }

    #[Computed]
    public function orders()
    {
        return $this->buildQuery()->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.orders.order-list')
            ->layout('layouts.admin', ['title' => 'Orders']);
    }
}
