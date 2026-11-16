<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ProductImageUploadController;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/media/{media}', [\App\Http\Controllers\MediaController::class, 'show'])
    ->whereUuid('media')->name('media.show');

Route::get('/product-images/{image}', function (ProductImage $image) {
    $blob = $image->blob;
    abort_unless($blob, 404);

    $data = $blob->image_data;
    if (is_resource($data)) {
        $data = stream_get_contents($data);
    }

    return response($data, 200, [
        'Content-Type' => $blob->mime_type,
        'Content-Length' => (string) ($blob->file_size ?: strlen($data)),
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->name('product-images.show');

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
    abort_unless(app(\App\Services\OrderService::class)->canViewReceipt($order), 404);
    return response()->view('pages.order-confirmation', compact('order'))
        ->header('Cache-Control', 'private, no-store')
        ->header('Referrer-Policy', 'no-referrer');
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
    if (is_string($cvPath) && preg_match('~^portfolio/cv/[a-zA-Z0-9_-]+\.(pdf|docx?)$~iD', $cvPath, $match)
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($cvPath)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->download($cvPath, 'Nasir_Ali_CV.'.strtolower($match[1]));
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

Route::post('/payments/stripe/webhook', \App\Http\Controllers\StripeWebhookController::class);

Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/ordering', 'pages.ordering')->name('ordering');
Route::post('/privacy/visitor-count', function (\Illuminate\Http\Request $request) {
    $request->validate(['allow' => ['required', 'boolean']]);
    $request->session()->put('allow_visitor_count', $request->boolean('allow'));
    if (!$request->boolean('allow')) {
        $record = $request->session()->pull('visitor_record');
        if ($record) {
            \App\Models\ActiveVisitor::where('session_id', $record)->delete();
        }
        $request->session()->forget('last_visitor_ping');
    }
    return redirect()->route('privacy')->with('account_message', 'Your preference has been saved.');
})->middleware('throttle:10,1')->name('privacy.visitors');
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::put('/account/password', [\App\Http\Controllers\AccountController::class, 'changePassword'])->name('account.password');
    Route::delete('/account', [\App\Http\Controllers\AccountController::class, 'destroy'])->name('account.destroy');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/products', \App\Livewire\Admin\Products\ProductList::class)->name('products.index');
    Route::get('/products/create', \App\Livewire\Admin\Products\ProductForm::class)->name('products.create');
    Route::get('/products/{id}/edit', \App\Livewire\Admin\Products\ProductForm::class)->name('products.edit');
    Route::post('/products/{product}/images/upload', [ProductImageUploadController::class, 'store'])->name('products.images.upload');
    Route::get('/products/{id}/inventory', \App\Livewire\Admin\Products\InventoryManager::class)->name('products.inventory');
    Route::get('/categories', \App\Livewire\Admin\Categories\CategoryManager::class)->name('categories.index');
    Route::get('/hero-slides', \App\Livewire\Admin\HeroSlides\SlideList::class)->name('hero-slides.index');
    Route::get('/orders', \App\Livewire\Admin\Orders\OrderList::class)->name('orders.index');
    Route::get('/orders/{id}', \App\Livewire\Admin\Orders\OrderDetail::class)->name('orders.show');
    Route::get('/newsletter', \App\Livewire\Admin\Newsletter\SubscriberList::class)->name('newsletter.index');
    Route::get('/portfolio', \App\Livewire\Admin\Portfolio\PortfolioManager::class)->name('portfolio.index');
    Route::get('/settings', \App\Livewire\Admin\Settings\StoreSettings::class)->name('settings.index');
});
