<div>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="font-poppins fw-bold mb-1">Orders Management</h2>
            <p class="text-muted small mb-0">Search, process, and track customer orders across Pakistan.</p>
        </div>
        <button wire:click="exportCsv" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Orders (CSV)
        </button>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by Order ID (CC-...), Phone, Name...">
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select wire:model.live="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Order::STATUSES as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-6">
                <label class="form-label small fw-semibold text-muted mb-1">Channel</label>
                <select wire:model.live="paymentMethod" class="form-select">
                    <option value="">All Channels</option>
                    <option value="whatsapp">WhatsApp Order</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="stripe">Online (Card)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <label class="form-label small fw-semibold text-muted mb-1">From Date</label>
                <input type="date" wire:model.live="dateFrom" class="form-control">
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <label class="form-label small fw-semibold text-muted mb-1">To Date</label>
                <input type="date" wire:model.live="dateTo" class="form-control">
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background:#F8FAFC">
                    <tr>
                        <th class="ps-3">Order ID</th>
                        <th>Customer Details (Admin Only)</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Channel</th>
                        <th>Created / Expiry</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->orders as $order)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold font-poppins text-primary text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                                @if($order->is_guest)
                                    <span class="badge bg-light text-muted border px-1" style="font-size:0.65rem">Guest</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->billing_name }}</div>
                                <div class="text-dark small d-flex align-items-center gap-1">
                                    <span>📞</span> <strong class="text-primary">{{ $order->billing_phone ?: 'No phone' }}</strong>
                                </div>
                                @if($order->billing_city)
                                    <div class="text-muted" style="font-size:0.75rem">📍 {{ $order->shipping_city ?: $order->billing_city }}</div>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">Rs. {{ number_format($order->total, 0) }}</div>
                                <small class="text-muted" style="font-size:0.75rem">Delivery: Rs. {{ number_format($order->shipping_amount, 0) }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm badge bg-{{ $order->status_badge_class }}-subtle text-{{ $order->status_badge_class }} border border-{{ $order->status_badge_class }}-subtle px-2 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ $order->status_label }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-sm shadow-sm">
                                        @foreach(\App\Models\Order::STATUSES as $opt)
                                            <li>
                                                <button class="dropdown-item small" type="button" wire:click="updateOrderStatus({{ $order->id }}, '{{ $opt }}')">
                                                    {{ ucwords(str_replace('_', ' ', $opt)) }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td>
                                @if($order->payment_method === 'whatsapp')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                        <span>💬</span> WhatsApp
                                    </span>
                                @elseif($order->payment_method === 'stripe')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        Card (Stripe)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                        COD
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                @if($order->status === 'pending_whatsapp')
                                    <small class="text-danger" style="font-size:0.75rem">Expires: {{ $order->created_at->addHours(24)->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="fs-1 mb-2">📦</div>
                                No orders found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->orders->hasPages())
            <div class="p-3 border-top">
                {{ $this->orders->links() }}
            </div>
        @endif
    </div>
</div>
