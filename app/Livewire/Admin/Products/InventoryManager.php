<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class InventoryManager extends Component
{
    public Product $product;

    public int $simpleStock = 0;

    public array $variantStocks = [];

    #[Locked]
    public array $loadedStock = [];

    public function mount(int $id): void
    {
        $this->product = Product::with(['allVariants.variantAttributes.attributeType'])->findOrFail($id);
        $this->simpleStock = $this->product->stock_quantity;

        foreach ($this->product->allVariants as $variant) {
            $this->variantStocks[$variant->id] = $variant->stock_quantity;
        }
        $this->loadedStock = ['quantity' => $this->simpleStock, 'variants' => $this->variantStocks, 'variable' => $this->product->is_variable];
    }

    public function save(): void
    {
        $this->validate(['simpleStock' => 'integer|min:0', 'variantStocks.*' => 'integer|min:0']);
        DB::transaction(function () {
            $product = Product::lockForUpdate()->findOrFail($this->product->id);
            $variants = $product->allVariants()->orderBy('id')->lockForUpdate()->get();
            $snapshot = ['quantity' => $product->stock_quantity, 'variants' => $variants->pluck('stock_quantity', 'id')->all(), 'variable' => $product->is_variable];
            if ($snapshot != $this->loadedStock) {
                throw ValidationException::withMessages(['inventory' => 'Stock changed since this screen opened. Reload before saving.']);
            }
            if ($product->is_variable) {
                $submittedIds = array_map('intval', array_keys($this->variantStocks));
                sort($submittedIds);
                if ($submittedIds !== $variants->modelKeys()) {
                    throw ValidationException::withMessages(['inventory' => 'The product options changed. Reload before saving.']);
                }
                $total = 0;
                foreach ($this->variantStocks as $variantId => $qty) {
                    $qty = max(0, (int) $qty);
                    $product->allVariants()->where('id', $variantId)->update(['stock_quantity' => $qty]);
                    $total += $qty;
                }
                $product->update(['stock_quantity' => $total]);
                $this->simpleStock = $total;
            } else {
                $product->update(['stock_quantity' => $this->simpleStock]);
            }
            $this->loadedStock = ['quantity' => $product->stock_quantity, 'variants' => $product->allVariants()->orderBy('id')->pluck('stock_quantity', 'id')->all(), 'variable' => $product->is_variable];
        }, 3);
        $this->product->refresh();

        $this->dispatch('show-toast', message: 'Inventory updated!', type: 'success');
    }

    public function adjustVariantStock(int $variantId, int $amount): void
    {
        $current = $this->variantStocks[$variantId] ?? 0;
        $this->variantStocks[$variantId] = max(0, $current + $amount);
    }

    public function render()
    {
        return view('livewire.admin.products.inventory-manager')
            ->layout('layouts.admin', ['title' => 'Inventory: '.$this->product->name]);
    }
}
