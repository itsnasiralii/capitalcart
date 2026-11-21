<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductBulkImportService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class ProductList extends Component
{
    use WithPagination, WithFileUploads;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $stock = '';

    #[Url]
    public string $sort = 'newest';

    public ?int $deleteId = null;

    // Bulk Import properties
    public bool $showBulkModal = false;
    public $csvFile = null;
    public float $bulkMarkup = 250;
    public int $bulkDefaultStock = 50;
    public string $bulkDefaultCategory = '';
    public bool $bulkUpdateExisting = true;
    public bool $isImporting = false;
    public string $importFeedback = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedStock(): void { $this->resetPage(); }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        $query = Product::with(['category'])->withTrashed(false);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        if ($this->stock === 'low') {
            $query->whereBetween('stock_quantity', [1, 10]);
        } elseif ($this->stock === 'out') {
            $query->where('stock_quantity', '<=', 0);
        }

        match ($this->sort) {
            'price_asc'  => $query->orderBy('base_price'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'name'       => $query->orderBy('name'),
            'stock_asc'  => $query->orderBy('stock_quantity'),
            default      => $query->latest(),
        };

        return $query->paginate(15);
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        $this->dispatch('show-toast', message: 'Product status updated.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function deleteProduct(): void
    {
        if ($this->deleteId) {
            Product::findOrFail($this->deleteId)->delete();
            $this->deleteId = null;
            $this->dispatch('show-toast', message: 'Product deleted.', type: 'success');
        }
    }

    public function openBulkModal(): void
    {
        $this->showBulkModal = true;
        $this->importFeedback = '';
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
        $this->csvFile = null;
    }

    public function runBulkImport(ProductBulkImportService $importer): void
    {
        $filePath = null;

        if ($this->csvFile) {
            $this->validate([
                'csvFile' => 'file|max:51200',
            ]);
            $filePath = $this->csvFile->getRealPath();
        } else {
            $defaultPath = database_path('data/products.csv');
            if (file_exists($defaultPath)) {
                $filePath = $defaultPath;
            } else {
                $this->addError('csvFile', 'Please select a CSV file or ensure database/data/products.csv is present.');
                return;
            }
        }

        $this->isImporting = true;

        try {
            $results = $importer->importFromCsv($filePath, [
                'markup'              => $this->bulkMarkup,
                'default_stock'       => $this->bulkDefaultStock,
                'default_category_id' => $this->bulkDefaultCategory ?: null,
                'update_existing'     => $this->bulkUpdateExisting,
            ]);

            $this->importFeedback = "Import complete! Created {$results['imported']} new products, updated {$results['updated']}.";
            $this->dispatch('show-toast', message: $this->importFeedback, type: 'success');
            $this->showBulkModal = false;
            $this->csvFile = null;
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->importFeedback = 'Error importing: ' . $e->getMessage();
            $this->dispatch('show-toast', message: $this->importFeedback, type: 'error');
        } finally {
            $this->isImporting = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.products.product-list')
            ->layout('layouts.admin', ['title' => 'Products']);
    }
}
