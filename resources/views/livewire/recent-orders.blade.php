<div>
    @if($orders->isNotEmpty())
        <div class="py-2" style="background:#F8FAFC;border-bottom:1px solid #E2E8F0">
            <div class="container">
                <div class="d-flex align-items-center justify-content-center gap-3 overflow-hidden text-center small text-muted flex-wrap">
                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1">
                        <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#22C55E"></span> Recent Order Activity
                    </span>
                    @php $latest = $orders->first(); @endphp
                    <div>
                        <strong class="text-dark">{{ $latest['masked_name'] }}</strong>
                        <span class="text-muted">({{ $latest['masked_phone'] }})</span> from <strong>{{ $latest['city'] }}</strong> recently placed an order
                        <span class="text-muted small ms-1">• {{ $latest['time_ago'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
