<?php

namespace App\Livewire\Admin\Categories;

use App\Helpers\PhoneHelper;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $statusFilter = '';

    // Form fields
    public bool $isEditing = false;
    public ?int $editingCategoryId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $whatsapp_number = '';
    public int $sort_order = 0;
    public bool $is_active = true;
    public $image = null;
    public ?string $existingImageUrl = null;

    // Delete modal safety
    public ?int $categoryToDeleteId = null;
    public int $categoryProductCount = 0;
    public ?int $reassignToCategoryId = null;
    public bool $showDeleteModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-category-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $category = Category::findOrFail($id);

        $this->isEditing = true;
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->whatsapp_number = $category->whatsapp_number ?? '';
        $this->sort_order = $category->sort_order ?? 0;
        $this->is_active = (bool) $category->is_active;
        $this->existingImageUrl = $category->image_url;

        $this->dispatch('open-category-modal');
    }

    public function saveCategory(): void
    {
        $slugRule = 'required|string|max:100|unique:categories,slug';
        if ($this->isEditing) {
            $slugRule .= ',' . $this->editingCategoryId;
        }

        $this->validate([
            'name'        => 'required|string|max:100',
            'slug'        => $slugRule,
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== '' && !PhoneHelper::isValidPakistaniNumber($value)) {
                        $fail('Enter a valid Pakistani mobile number, for example 03009362584.');
                    }
                },
            ],
            'sort_order'  => 'required|integer|min:0|max:9999',
            'is_active'   => 'boolean',
            'image'       => 'nullable|mimes:jpeg,jpg,png,webp,avif,gif|max:5120',
        ]);

        $imageUrl = $this->existingImageUrl;

        if ($this->image) {
            // Delete old stored image if it was local
            if ($this->existingImageUrl && str_starts_with($this->existingImageUrl, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $this->existingImageUrl);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->image->store('categories', 'public');
            $imageUrl = '/storage/' . $path;
        }

        Category::updateOrCreate(
            ['id' => $this->editingCategoryId],
            [
                'name'        => $this->name,
                'slug'        => Str::slug($this->slug),
                'description' => $this->description ?: null,
                'whatsapp_number' => $this->whatsapp_number !== ''
                    ? PhoneHelper::toLocal($this->whatsapp_number)
                    : null,
                'sort_order'  => $this->sort_order,
                'is_active'   => $this->is_active,
                'image_url'   => $imageUrl,
            ]
        );

        $this->dispatch('close-category-modal');
        $this->resetForm();
        $this->dispatch('show-toast', message: $this->isEditing ? 'Category updated successfully!' : 'Category created successfully!', type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $this->dispatch('show-toast', message: 'Category status updated.', type: 'info');
    }

    public function confirmDelete(int $id): void
    {
        $category = Category::withCount('products')->findOrFail($id);
        $this->categoryToDeleteId = $category->id;
        $this->categoryProductCount = $category->products_count;
        $this->reassignToCategoryId = null;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->categoryToDeleteId = null;
        $this->categoryProductCount = 0;
        $this->reassignToCategoryId = null;
    }

    public function deleteCategory(): void
    {
        if (!$this->categoryToDeleteId) {
            return;
        }

        $category = Category::withCount('products')->findOrFail($this->categoryToDeleteId);

        // If products exist and no reassign target selected, prevent deletion!
        if ($category->products_count > 0 && !$this->reassignToCategoryId) {
            $this->addError('reassign', 'This category contains ' . $category->products_count . ' products. Please select a category to reassign them to before deleting.');
            return;
        }

        // Reassign products if requested
        if ($category->products_count > 0 && $this->reassignToCategoryId) {
            $target = Category::findOrFail($this->reassignToCategoryId);
            $category->products()->update(['category_id' => $target->id]);
        }

        // Clean up stored image
        if ($category->image_url && str_starts_with($category->image_url, '/storage/')) {
            $path = str_replace('/storage/', '', $category->image_url);
            Storage::disk('public')->delete($path);
        }

        $category->delete();

        $this->showDeleteModal = false;
        $this->categoryToDeleteId = null;
        $this->dispatch('show-toast', message: 'Category deleted safely.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingCategoryId',
            'name',
            'slug',
            'description',
            'whatsapp_number',
            'sort_order',
            'is_active',
            'image',
            'existingImageUrl',
            'isEditing',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Category::withCount('products');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $categories = $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->paginate(10);
        $otherCategories = $this->categoryToDeleteId
            ? Category::where('id', '!=', $this->categoryToDeleteId)->get()
            : collect();

        return view('livewire.admin.categories.category-manager', [
            'categories' => $categories,
            'otherCategories' => $otherCategories,
        ])->layout('layouts.admin', ['title' => 'Categories']);
    }
}
