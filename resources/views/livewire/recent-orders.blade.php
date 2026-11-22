<div>
    @if($orders->isNotEmpty())
        @php $latest = $orders->first(); @endphp
        <aside class="recent-order-popup" role="status" aria-live="polite" aria-atomic="true"
            x-data="{
                visible: false, showTimer: null, hideTimer: null,
                init() {
                    this.showTimer = setTimeout(() => {
                        this.visible = true;
                        this.hideTimer = setTimeout(() => { this.visible = false; }, 6000);
                    }, 2000);
                },
                dismiss() { this.visible = false; clearTimeout(this.showTimer); clearTimeout(this.hideTimer); },
                destroy() { clearTimeout(this.showTimer); clearTimeout(this.hideTimer); }
            }"
            x-show="visible" style="display:none">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                <span class="text-success fw-semibold small">Recent Order Activity</span>
                <button type="button" class="btn-close" aria-label="Dismiss recent order notification" @click="dismiss()"></button>
            </div>
            <p class="mb-1 small">
                <strong>{{ $latest['masked_name'] }}</strong>
                <span class="text-muted">({{ $latest['masked_phone'] }})</span>
                from <strong>{{ $latest['city'] }}</strong> placed an order.
            </p>
            <span class="text-muted small">{{ $latest['time_ago'] }}</span>
        </aside>
    @endif
</div>
