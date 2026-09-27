<?php

namespace App\Livewire\Admin\HeroSlides;

use App\Models\HeroSlide;
use App\Services\ImageStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SlideList extends Component
{
    use WithPagination, WithFileUploads;

    public bool $isEditing = false;
    public ?int $editingSlideId = null;
    public string $title = '';
    public string $subtitle = '';
    public string $button_text = 'Shop Now';
    public string $link_url = '/shop';
    public int $sort_order = 1;
    public bool $is_active = true;
    public $image = null;
    public ?string $existingImagePath = null;

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->dispatch('open-slide-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $slide = HeroSlide::findOrFail($id);

        $this->isEditing = true;
        $this->editingSlideId = $slide->id;
        $this->title = $slide->title;
        $this->subtitle = $slide->subtitle ?? '';
        $this->button_text = $slide->button_text ?? 'Shop Now';
        $this->link_url = $slide->link_url ?? '/shop';
        $this->sort_order = $slide->sort_order ?? 1;
        $this->is_active = (bool) $slide->is_active;
        $this->existingImagePath = $slide->image_path;

        $this->dispatch('open-slide-modal');
    }

    public function saveSlide(): void
    {
        $imageRule = array_merge([$this->isEditing ? 'nullable' : 'required'], array_slice(ImageStorage::rules(), 1));

        $this->validate([
            'title'       => 'required|string|max:150',
            'subtitle'    => 'nullable|string|max:255',
            'button_text' => 'required|string|max:50',
            'link_url'    => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                $relative = preg_match('#^/(?!/)[^\\\\\\s]*$#', $value);
                $absolute = filter_var($value, FILTER_VALIDATE_URL)
                    && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);
                if ($value && !$relative && !$absolute) {
                    $fail('Use a store path such as /shop or an http(s) URL.');
                }
            }],
            'sort_order'  => 'required|integer|min:0|max:9999',
            'is_active'   => 'boolean',
            'image'       => $imageRule,
        ]);

        try {
            DB::transaction(function () {
                $slide = $this->editingSlideId ? HeroSlide::findOrFail($this->editingSlideId) : new HeroSlide;
                $imagePath = $this->image ? app(ImageStorage::class)->store($this->image) : $slide->image_path;
                $slide->fill([
                    'title' => $this->title,
                    'subtitle' => $this->subtitle ?: null,
                    'button_text' => $this->button_text,
                    'link_url' => $this->link_url ?: '/shop',
                    'sort_order' => $this->sort_order,
                    'is_active' => $this->is_active,
                    'image_path' => $imagePath,
                ])->save();
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('image', 'The slide could not be saved. Your previous image is unchanged. Please retry.');
            return;
        }

        $message = $this->isEditing ? 'Hero slide updated successfully!' : 'Hero slide added successfully!';
        $this->dispatch('close-slide-modal');
        $this->resetForm();
        $this->dispatch('show-toast', message: $message, type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->is_active = !$slide->is_active;
        $slide->save();

        $this->dispatch('show-toast', message: 'Slide status changed.', type: 'info');
    }

    public function deleteSlide(int $id): void
    {
        $slide = HeroSlide::findOrFail($id);

        if ($slide->image_path && !str_starts_with($slide->image_path, 'http') && !str_starts_with($slide->image_path, '/media/')) {
            $cleanPath = str_replace(['storage/', '/storage/'], '', $slide->image_path);
            Storage::disk('public')->delete($cleanPath);
        }

        $slide->delete();
        $this->dispatch('show-toast', message: 'Slide deleted.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingSlideId',
            'title',
            'subtitle',
            'button_text',
            'link_url',
            'sort_order',
            'is_active',
            'image',
            'existingImagePath',
            'isEditing',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $slides = HeroSlide::orderBy('sort_order', 'asc')->paginate(10);

        return view('livewire.admin.hero-slides.slide-list', [
            'slides' => $slides,
        ])->layout('layouts.admin', ['title' => 'Hero Slides']);
    }
}
