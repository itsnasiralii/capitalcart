import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

function openCartSidebar() {
    const el = document.getElementById('cartSidebar');
    if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
}

// Livewire.on registration — works in both livewire:init and livewire:initialized
document.addEventListener('livewire:init', () => {
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
