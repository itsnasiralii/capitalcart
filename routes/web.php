<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/media/{media}', [\App\Http\Controllers\MediaController::class, 'show'])
    ->whereUuid('media')->name('media.show');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', function () {
    return view('pages.shop');
})->name('shop');

Route::get('/product/{slug}', function (string $slug) {
    $product = Product::with(['images', 'category'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('pages.product', compact('product'));
})->name('product.show');

Route::get('/cart', function () {
    return view('pages.cart');
})->name('cart');

Route::get('/wishlist', function () {
    return view('pages.wishlist');
})->name('wishlist');

Route::get('/checkout', function () {
    return view('pages.checkout');
})->name('checkout');

Route::get('/order-confirmation/{orderNumber}', function (string $orderNumber) {
    $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
    return view('pages.order-confirmation', compact('order'));
})->name('order.confirmation');

Route::get('/order-confirmation/{orderNumber}/whatsapp', function (string $orderNumber) {
    $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
    $service = app(\App\Services\OrderService::class);
    abort_unless($service->canOpenWhatsApp($order), 403, 'Open this order from the browser used at checkout, within 24 hours.');

    return redirect()->away($service->getWhatsAppUrlForOrder($order))
        ->header('Cache-Control', 'private, no-store')
        ->header('Referrer-Policy', 'no-referrer');
})->name('order.whatsapp');

Route::get('/portfolio', function () {
    return view('pages.portfolio');
})->name('portfolio');

Route::get('/portfolio/download-cv', function () {
    $cvPath = setting('portfolio_cv_file');
    if ($cvPath && file_exists(public_path('storage/' . $cvPath))) {
        return response()->download(public_path('storage/' . $cvPath), 'Nasir_Ali_CV.pdf');
    }

    $cvContent = "NASIR ALI — NETWORK ENGINEER & SOFTWARE DEVELOPER\n"
        . "Location: Islamabad, Pakistan | Contact: 03002922584 | Email: nasirali@capitalcart.pk\n"
        . "LinkedIn: https://linkedin.com/in/itsnasiralii | GitHub: https://github.com/itsnasiralii\n\n"
        . "PROFILE INTRODUCTION:\n"
        . "I am a full-time Network Engineer at Zong CMPak and a part-time Software Developer with a deep analytical mindset.\n"
        . "I specialize in examining large-scale and hyperscale enterprise systems, identifying technical weaknesses,\n"
        . "troubleshooting complex issues, and developing practical solutions. I am passionate about innovative ideas,\n"
        . "emerging technologies, network automation, and building systems that solve real-world problems.\n"
        . "I am flexible, adaptable, and willing to travel whenever professional opportunities require it.\n\n"
        . "PROFESSIONAL EXPERIENCE:\n"
        . "1. Corporate NOC Engineer — Zong CMPak (January 2026 – Present)\n"
        . "   - Monitor and support enterprise network services\n"
        . "   - Troubleshoot complex connectivity and service incidents\n"
        . "   - Coordinate with Core, RAN, Transmission, and vendor teams\n"
        . "   - Support VoIP, SIP, PRI, vPBX, IP, and enterprise services\n"
        . "   - Handle incident documentation, RCA, escalation, and SLA monitoring\n"
        . "   - Develop Python-based network-diagnostic and operational tools\n\n"
        . "2. Network Engineer (TAC) — Cybernet (July 2025 – December 2025)\n"
        . "   - Provided Tier-2 technical support\n"
        . "   - Troubleshot enterprise-network connectivity issues\n"
        . "   - Supported backbone and customer services\n"
        . "   - Coordinated escalations and assisted junior technical resources\n\n"
        . "3. Part-Time Software Developer\n"
        . "   - Build web applications, automation tools, dashboards, and operational utilities\n"
        . "   - Work with Python, PHP, Laravel, JavaScript, HTML, CSS, MySQL, and APIs\n"
        . "   - Convert operational problems into practical software solutions\n\n"
        . "TECHNICAL SKILLS:\n"
        . "- Networking: TCP/IP, BGP, OSPF, VLANs, DNS, DHCP, IPv6\n"
        . "- Telecom: 5G, LTE, RAN, SIP, PRI, VoIP, vPBX\n"
        . "- Network Tools: Huawei NE40, Wireshark, OpenText NMS, SolarWinds, Zabbix, EVE-NG\n"
        . "- Development: Python, PHP, Laravel, JavaScript, HTML, CSS, MySQL\n"
        . "- Automation: Netmiko, Scapy, PowerShell, shell scripting\n"
        . "- Operations: Incident Management, RCA, SLA Management, Vendor Coordination\n"
        . "- Security: CCNA, CompTIA Security+, CEH knowledge\n";

    return response($cvContent)
        ->header('Content-Type', 'text/plain')
        ->header('Content-Disposition', 'attachment; filename="Nasir_Ali_Resume.txt"');
})->name('portfolio.cv.download');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/products', \App\Livewire\Admin\Products\ProductList::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Admin\Products\ProductForm::class)->name('products.create');
    Route::get('/products/{id}/edit', \App\Livewire\Admin\Products\ProductForm::class)->name('products.edit');
    Route::get('/products/{id}/inventory', \App\Livewire\Admin\Products\InventoryManager::class)->name('products.inventory');
    Route::get('/categories', \App\Livewire\Admin\Categories\CategoryManager::class)->name('categories.index');
    Route::get('/hero-slides', \App\Livewire\Admin\HeroSlides\SlideList::class)->name('hero-slides.index');
    Route::get('/orders', \App\Livewire\Admin\Orders\OrderList::class)->name('orders.index');
    Route::get('/orders/{id}', \App\Livewire\Admin\Orders\OrderDetail::class)->name('orders.show');
    Route::get('/newsletter', \App\Livewire\Admin\Newsletter\SubscriberList::class)->name('newsletter.index');
    Route::get('/portfolio', \App\Livewire\Admin\Portfolio\PortfolioManager::class)->name('portfolio.index');
    Route::get('/settings', \App\Livewire\Admin\Settings\StoreSettings::class)->name('settings.index');
});
