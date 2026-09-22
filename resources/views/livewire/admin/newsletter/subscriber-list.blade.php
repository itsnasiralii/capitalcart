<div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="font-poppins fw-bold mb-1">Newsletter Subscribers</h2>
            <p class="text-muted small mb-0">Manage customer newsletter subscriptions and export audience data.</p>
        </div>
        <button wire:click="exportCsv" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export to CSV
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small">Total Subscribers</div>
                <div class="fs-3 fw-bold font-poppins text-primary mt-1">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small">Active Audience</div>
                <div class="fs-3 fw-bold font-poppins text-success mt-1">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small">Unsubscribed</div>
                <div class="fs-3 fw-bold font-poppins text-muted mt-1">{{ number_format($stats['unsubscribed']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small">New This Month</div>
                <div class="fs-3 fw-bold font-poppins text-warning mt-1">{{ number_format($stats['this_month']) }}</div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by subscriber email...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="unsubscribed">Unsubscribed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background:#F8FAFC">
                    <tr>
                        <th class="ps-3">Subscriber Email</th>
                        <th>Status</th>
                        <th>Subscribed Date</th>
                        <th>Unsubscribed Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr>
                        <td class="ps-3 fw-semibold">
                            {{ $sub->email }}
                        </td>
                        <td>
                            @if($sub->status === 'active')
                                <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Unsubscribed</span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted">{{ $sub->subscribed_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="small text-muted">{{ $sub->unsubscribed_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="toggleStatus({{ $sub->id }})" class="btn btn-outline-secondary" title="{{ $sub->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    {{ $sub->status === 'active' ? 'Deactivate' : 'Reactivate' }}
                                </button>
                                <button wire:click="deleteSubscriber({{ $sub->id }})" wire:confirm="Remove this email from database?" class="btn btn-outline-danger" title="Delete">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-2">✉️</div>
                            No subscribers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
            <div class="p-3 border-top">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
</div>
