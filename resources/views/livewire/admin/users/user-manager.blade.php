<div>
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="font-poppins fw-bold mb-1">Admin Staff &amp; Access Control</h2>
            <p class="text-muted small mb-0">Create, manage, and grant administrative credentials and staff roles for CapitalCart.pk.</p>
        </div>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2" wire:click="openCreateModal">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="fw-semibold">Add New Admin</span>
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:rgba(249,115,22,0.12);color:#F97316;">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.4 0 2.5 1.1 2.5 2.5S13.4 12 12 12s-2.5-1.1-2.5-2.5S10.6 7 12 7zm0 10.9c-2.3 0-4.3-1.1-5.5-2.8.03-1.8 3.7-2.8 5.5-2.8s5.47 1 5.5 2.8c-1.2 1.7-3.2 2.8-5.5 2.8z"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.05em">Active Admins</div>
                        <h4 class="mb-0 fw-bold">{{ $totalAdmins }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:rgba(59,130,246,0.12);color:#3B82F6;">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;letter-spacing:0.05em">Total Registered Users</div>
                        <h4 class="mb-0 fw-bold">{{ $totalUsers }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="col-md-6 col-lg-8 d-flex justify-content-md-end gap-2 flex-wrap">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm {{ $roleFilter === 'admin' ? 'btn-dark' : 'btn-outline-secondary' }}" wire:click="$set('roleFilter', 'admin')">
                            🛡️ Admins Only ({{ $totalAdmins }})
                        </button>
                        <button type="button" class="btn btn-sm {{ $roleFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}" wire:click="$set('roleFilter', 'all')">
                            All Accounts ({{ $totalUsers }})
                        </button>
                        <button type="button" class="btn btn-sm {{ $roleFilter === 'customer' ? 'btn-dark' : 'btn-outline-secondary' }}" wire:click="$set('roleFilter', 'customer')">
                            Customers Only
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase small text-muted" style="letter-spacing:0.05em">User</th>
                        <th class="py-3 text-uppercase small text-muted" style="letter-spacing:0.05em">Email Credentials</th>
                        <th class="py-3 text-uppercase small text-muted" style="letter-spacing:0.05em">Access Role</th>
                        <th class="py-3 text-uppercase small text-muted" style="letter-spacing:0.05em">Registered On</th>
                        <th class="pe-4 py-3 text-end text-uppercase small text-muted" style="letter-spacing:0.05em">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" 
                                         style="width:38px;height:38px;background:{{ $user->is_admin ? '#F97316' : '#64748B' }};font-size:0.875rem">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-1.5">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.65rem">You (Logged In)</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">ID: #{{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="font-monospace text-dark" style="font-size:0.875rem">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                @if($user->is_admin)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 px-2.5 py-1" style="font-size:0.75rem">
                                        <span>🛡️</span>
                                        <strong>Administrator</strong>
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1" style="font-size:0.75rem">
                                        Customer
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-muted small">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    {{-- Edit button --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" wire:click="openEditModal({{ $user->id }})" title="Edit Credentials">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                    </button>

                                    {{-- Toggle Admin Status (if not self) --}}
                                    @if($user->id !== auth()->id())
                                        <button type="button" 
                                                class="btn btn-sm {{ $user->is_admin ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                wire:click="toggleAdminStatus({{ $user->id }})" 
                                                title="{{ $user->is_admin ? 'Revoke Admin Access' : 'Promote to Admin' }}">
                                            {{ $user->is_admin ? 'Demote' : 'Make Admin' }}
                                        </button>

                                        {{-- Delete User --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" wire:click="confirmDelete({{ $user->id }})" title="Delete Account">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <span class="fs-1 d-block mb-2">👤</span>
                                    <p class="mb-0 fw-semibold">No accounts found matching your criteria.</p>
                                    <small>Try clearing the search filter or change role tabs.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4">{{ $isEditing ? '✏️' : '🛡️' }}</span>
                            <div>
                                <h5 class="modal-title font-poppins fw-bold mb-0">
                                    {{ $isEditing ? 'Edit User & Credentials' : 'Create New Admin Account' }}
                                </h5>
                                <small class="text-muted">
                                    {{ $isEditing ? 'Update profile, reset password, or adjust access level' : 'Grant administrator privileges to another team member' }}
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit.prevent="saveUser">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Asad Khan">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Login Email Address <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="admin@capitalcart.pk">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">This email is used to sign in to the Admin Dashboard.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    {{ $isEditing ? 'New Password (leave blank to keep current)' : 'Password' }}
                                    @if(!$isEditing) <span class="text-danger">*</span> @endif
                                </label>
                                <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 8 characters">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Confirm Password
                                    @if(!$isEditing) <span class="text-danger">*</span> @endif
                                </label>
                                <input type="password" wire:model="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Re-type password">
                                @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="p-3 bg-light rounded-3 border mt-3">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" wire:model="is_admin" class="form-check-input" id="adminRoleSwitch" 
                                           @if($isEditing && $editingUserId === auth()->id()) disabled @endif>
                                    <label class="form-check-label fw-semibold ms-2" for="adminRoleSwitch">
                                        Grant Full Administrator Access
                                        <small class="text-muted d-block fw-normal">Enables full access to products, orders, inventory, settings, and developer controls.</small>
                                    </label>
                                </div>
                                @error('is_admin') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-top py-3">
                            <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveUser" class="spinner-border spinner-border-sm me-1"></span>
                                {{ $isEditing ? 'Update User' : 'Create Admin' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-danger text-white py-3">
                        <h5 class="modal-title font-poppins fw-bold mb-0">Delete Account Confirmation</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Are you sure you want to permanently delete the account for <strong class="text-dark">{{ $userToDeleteName }}</strong>?</p>
                        <p class="text-muted small mb-0">This user will immediately lose access to the system. This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer bg-light border-top py-3">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" wire:click="deleteUser" wire:loading.attr="disabled">
                            <span wire:loading wire:target="deleteUser" class="spinner-border spinner-border-sm me-1"></span>
                            Yes, Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
