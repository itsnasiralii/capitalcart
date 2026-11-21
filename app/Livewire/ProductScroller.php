<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductScroller extends Component
{
    public int $perBatch = 10;
    public int $loadedCount = 10;
    public bool $hasMore = true;

    public function mount(int $initial = 10): void
    {
        $this->loadedCount = $initial;
        $total = Product::active()->inStock()->count();
        $this->hasMore = $this->loadedCount < $total;
    }

    public function loadMore(): void
    {
        $total = Product::active()->inStock()->count();
        $this->loadedCount += $this->perBatch;
        if ($this->loadedCount >= $total) {
            $this->hasMore = false;
        }
    }

    public function render()
    {
        $products = Product::active()
            ->inStock()
            ->with(['category', 'images'])
            ->orderByRaw('CASE WHEN sale_price IS NOT NULL THEN 0 ELSE 1 END, id DESC')
            ->take($this->loadedCount)
            ->get();

        return view('livewire.product-scroller', [
            'products' => $products,
        ]);
    }
}
