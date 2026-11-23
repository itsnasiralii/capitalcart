import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

function openCartSidebar() {
    const el = document.getElementById('cartSidebar');
    if (!el) return;
    if (!el.classList.contains('show')) {
        document.querySelectorAll('.offcanvas-backdrop:not(.show)').forEach(b => b.remove());
    }
    const instance = bootstrap.Offcanvas.getOrCreateInstance(el);
    instance.show();
}

function attachOffcanvasCleanup() {
    const el = document.getElementById('cartSidebar');
    if (el && !el._cleanupAttached) {
        el._cleanupAttached = true;
        el.addEventListener('hidden.bs.offcanvas', () => {
            document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachOffcanvasCleanup);
} else {
    attachOffcanvasCleanup();
}
document.addEventListener('livewire:navigated', attachOffcanvasCleanup);

// Livewire.on registration — works in both livewire:init and livewire:initialized
document.addEventListener('livewire:init', () => {
    attachOffcanvasCleanup();
    Livewire.on('open-cart-sidebar', openCartSidebar);
    Livewire.on('show-toast', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        showToast(payload?.message || (typeof payload === 'string' ? payload : 'Done!'), payload?.type || 'success');
    });
});

// Fallback: Livewire 3 also dispatches as window CustomEvents
window.addEventListener('open-cart-sidebar', openCartSidebar);

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const colors = {
        success: 'bg-success',
        error:   'bg-danger',
        warning: 'bg-warning',
        info:    'bg-primary',
    };

    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast align-items-center text-white ${colors[type] || 'bg-success'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-medium"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close notification"></button>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    toastEl.querySelector('.toast-body').textContent = message;
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

window.toggleSidebar = function () {
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebar) sidebar.classList.toggle('open');
};

window.switchImage = function (url, el) {
    const main = document.getElementById('mainProductImage');
    if (main) main.src = url;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
};
