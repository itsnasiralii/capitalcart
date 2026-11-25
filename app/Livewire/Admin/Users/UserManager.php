<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filters & Search
    public string $search = '';
    public string $roleFilter = 'admin'; // 'all', 'admin', 'customer'

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingUserId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_admin = true;

    // Delete confirmation
    public ?int $userToDeleteId = null;
    public ?string $userToDeleteName = null;
    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        $passwordRule = $this->isEditing ? ['nullable', 'min:8', 'confirmed'] : ['required', 'min:8', 'confirmed'];

        return [
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'password'              => $passwordRule,
            'password_confirmation' => [$this->isEditing ? 'nullable' : 'required'],
            'is_admin'              => ['boolean'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->editingUserId = null;
        $this->is_admin = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $this->resetForm();

        $user = User::findOrFail($id);
        $this->isEditing = true;
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = (bool) $user->is_admin;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function saveUser(): void
    {
        $this->validate();

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingUserId);

            // Prevent self-demotion
            if ($user->id === auth()->id() && !$this->is_admin) {
                $this->addError('is_admin', 'You cannot remove admin privileges from your own active account.');
                return;
            }

            $user->name = $this->name;
            $user->email = $this->email;
            $user->is_admin = $this->is_admin;

            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }

            $user->save();

            $this->dispatch('show-toast', message: 'User updated successfully!', type: 'success');
        } else {
            User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
                'is_admin' => $this->is_admin,
            ]);

            $this->dispatch('show-toast', message: 'New admin account created successfully! Credentials ready.', type: 'success');
        }

        $this->closeModal();
    }

    public function toggleAdminStatus(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('show-toast', message: 'You cannot change your own admin access level.', type: 'error');
            return;
        }

        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();

        $statusText = $user->is_admin ? 'Admin privileges granted.' : 'Admin privileges revoked.';
        $this->dispatch('show-toast', message: "{$user->name}: {$statusText}", type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('show-toast', message: 'You cannot delete your own account while logged in.', type: 'error');
            return;
        }

        $user = User::findOrFail($id);
        $this->userToDeleteId = $user->id;
        $this->userToDeleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->userToDeleteId = null;
        $this->userToDeleteName = null;
    }

    public function deleteUser(): void
    {
        if (!$this->userToDeleteId || $this->userToDeleteId === auth()->id()) {
            $this->cancelDelete();
            return;
        }

        // Verify there is at least one other admin remaining if deleting an admin
        $user = User::findOrFail($this->userToDeleteId);
        if ($user->is_admin) {
            $otherAdminsCount = User::where('is_admin', true)->where('id', '!=', $user->id)->count();
            if ($otherAdminsCount < 1) {
                $this->dispatch('show-toast', message: 'Cannot delete the only remaining admin in the system.', type: 'error');
                $this->cancelDelete();
                return;
            }
        }

        $deletedName = $user->name;
        $user->delete();

        $this->cancelDelete();
        $this->dispatch('show-toast', message: "Account for {$deletedName} deleted successfully.", type: 'success');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_admin = true;
        $this->editingUserId = null;
        $this->isEditing = false;
    }

    public function render()
    {
        $query = User::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter === 'admin') {
            $query->where('is_admin', true);
        } elseif ($this->roleFilter === 'customer') {
            $query->where('is_admin', false);
        }

        $users = $query->orderByDesc('is_admin')
                      ->orderBy('name')
                      ->paginate(15);

        $totalAdmins = User::where('is_admin', true)->count();
        $totalUsers = User::count();

        return view('livewire.admin.users.user-manager', [
            'users'       => $users,
            'totalAdmins' => $totalAdmins,
            'totalUsers'  => $totalUsers,
        ])->layout('layouts.admin', ['title' => 'Admin Staff & Users']);
    }
}
