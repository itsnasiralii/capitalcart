<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ str_contains($title ?? '', 'CapitalCart') ? $title : (($title ? $title . ' | ' : '') . 'CapitalCart.pk — Everyday Essentials & B2B Wholesale in Pakistan') }}</title>
    <meta name="description" content="{{ $description ?? 'CapitalCart.pk is Pakistan’s trusted online store and B2B wholesale platform in Islamabad. Shop trending kitchen gadgets, home essentials, and daily utility goods with fast delivery across Pakistan.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CapitalCart.pk">
    <meta property="og:locale" content="en_PK">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ str_contains($title ?? '', 'CapitalCart') ? $title : (($title ? $title . ' | ' : '') . 'CapitalCart.pk — Everyday Essentials & B2B Wholesale') }}">
    <meta property="og:description" content="{{ $description ?? 'CapitalCart.pk is Pakistan’s trusted online store and B2B wholesale platform in Islamabad. Shop trending kitchen gadgets, home essentials, and daily utility goods.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/hero-fallback.svg') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@capitalcartpk">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ str_contains($title ?? '', 'CapitalCart') ? $title : (($title ? $title . ' | ' : '') . 'CapitalCart.pk — Everyday Essentials & B2B Wholesale') }}">
    <meta name="twitter:description" content="{{ $description ?? 'CapitalCart.pk is Pakistan’s trusted online store and B2B wholesale platform in Islamabad. Shop trending kitchen gadgets, home essentials, and daily utility goods.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/hero-fallback.svg') }}">

    {{-- Schema.org JSON-LD: Official OnlineStore & WebSite Knowledge Graph for Google & AI Overviews --}}
    @php
        $schemaGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'OnlineStore',
                    '@id' => url('/') . '/#store',
                    'name' => 'CapitalCart.pk',
                    'alternateName' => [
                        'CapitalCart',
                        'Capital Cart',
                        'Capital Cart Pakistan',
                        'CapitalCart PK',
                    ],
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/hero-fallback.svg'),
                        'caption' => 'CapitalCart.pk',
                    ],
                    'image' => asset('images/hero-fallback.svg'),
                    'description' => 'CapitalCart.pk is an online store and B2B wholesale supplier based in Islamabad, Pakistan, specializing in high-demand household essentials, kitchen gadgets, electronics, and daily utility products.',
                    'telephone' => setting('whatsapp_number', '+923000000000'),
                    'priceRange' => 'PKR',
                    'currenciesAccepted' => 'PKR',
                    'paymentAccepted' => 'Cash on Delivery',
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Pakistan',
                    ],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => 'Islamabad',
                        'addressRegion' => 'Islamabad Capital Territory',
                        'addressCountry' => 'PK',
                    ],
                    'sameAs' => [
                        'https://www.facebook.com/capitalcartofficial',
                        'https://www.instagram.com/capitalcart.pk',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '/#website',
                    'url' => url('/'),
                    'name' => 'CapitalCart.pk',
                    'alternateName' => 'CapitalCart',
                    'description' => "Pakistan's Online Store for Everyday Essentials & B2B Wholesale",
                    'publisher' => [
                        '@id' => url('/') . '/#store',
                    ],
                    'inLanguage' => 'en-PK',
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/shop') . '?search={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schemaGraph, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/storefront-design.css') }}?v={{ filemtime(public_path('css/storefront-design.css')) }}">
    @livewireStyles
    @stack('styles')
</head>
<body class="{{ request()->routeIs('portfolio') ? 'portfolio-mode' : '' }}" style="{{ request()->routeIs('portfolio') ? 'background-color: #070B14 !important; color: #E2E8F0 !important;' : '' }}">

{{-- Navbar --}}
<nav class="navbar navbar-marketory navbar-expand-lg sticky-top" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="{{ route('home') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span>Capital<span style="color:#F97316">Cart</span><span class="small" style="font-size:0.75rem;opacity:0.85">.pk</span></span>
        </a>
        <div class="d-flex align-items-center gap-1 d-lg-none">
            <button class="navbar-toggler border-0 p-2 text-white shadow-none" type="button" aria-label="Toggle navigation" aria-controls="navMenu" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto ms-3 gap-1 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shop') && !request('category') && !request('sort') ? 'active' : '' }}" href="{{ route('shop') }}">Shop</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-inline-flex align-items-center gap-1 {{ request('category') ? 'active' : '' }}" href="{{ route('shop') }}" id="categoryNavDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0 py-2" aria-labelledby="categoryNavDropdown2" style="background:#1E293B; max-height: 400px; overflow-y: auto; min-width: 250px; border-radius: 0.75rem;">
                        <li><a class="dropdown-item py-2 px-3 fw-600" href="{{ route('shop') }}" style="color:#F97316">All Categories &rarr;</a></li>
                        <li><hr class="dropdown-divider border-secondary my-1"></li>
                        @php
                            $navCategories = \App\Models\Category::active()->withCount('products')->orderByDesc('products_count')->take(15)->get();
                        @endphp
                        @foreach($navCategories as $cat)
                            <li>
                                <a class="dropdown-item py-1.5 px-3 text-white-50 text-truncate d-flex justify-content-between align-items-center" style="font-size:0.875rem;" href="{{ route('shop', ['category' => $cat->slug]) }}" title="{{ $cat->name }}">
                                    <span>{{ Str::limit($cat->name, 28) }}</span>
                                    <span class="badge bg-secondary ms-2" style="font-size:0.65rem">{{ $cat->products_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->boolean('onSale') ? 'active' : '' }}" href="{{ route('shop', ['onSale' => 1]) }}">Deals</a>
                </li>
            </ul>
            {{-- Mobile Search inside menu --}}
            <form action="{{ route('shop') }}" method="GET" class="d-lg-none my-3">
                <div class="input-group">
                    <input type="text" name="search" aria-label="Search products" value="{{ request('search') }}" class="form-control bg-dark text-white border-secondary" placeholder="Search products..." style="font-size:0.875rem; border-radius: 8px 0 0 8px;">
                    <button class="btn btn-accent text-white px-3" type="submit" aria-label="Search" style="background:#F97316; border-radius: 0 8px 8px 0;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
            <div class="d-flex align-items-center gap-3">
                {{-- Live Visitor Counter --}}
                @if(setting('show_visitor_counter', 1))
                    <span class="badge bg-white text-dark border d-none d-lg-inline-flex align-items-center gap-1" style="font-size:0.75rem;padding:0.4rem 0.65rem">
                        <span style="width:7px;height:7px;border-radius:50%;background:#22C55E;display:inline-block"></span>
                        <strong>{{ \App\Models\ActiveVisitor::getActiveCount() }}</strong> shoppers online
                    </span>
                @endif

                {{-- Cart Icon --}}
                <livewire:cart.cart-icon />

                {{-- Customer Auth --}}
                @auth
                    <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-sm" style="background:#F97316;color:#fff">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Recent Orders Activity Bar --}}
<livewire:recent-orders />

{{-- Cart Sidebar --}}
<livewire:cart.cart-sidebar />

{{-- Toast Container --}}
<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

{{-- Main Content --}}
<main>
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    {{ $slot }}
</main>

{{-- Footer --}}
@if(!request()->routeIs('portfolio'))
{{-- Trust Badges Strip (Daraz / Shopify style) --}}
<section class="footer-features py-4" style="background:#080D1A;border-top:1px solid rgba(255,255,255,0.08);border-bottom:1px solid rgba(255,255,255,0.06);">
    <div class="container">
        <div class="row g-3 text-white">
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:rgba(249,115,22,0.15);color:#F97316;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:0.875rem">Nationwide Delivery</h6>
                    <small class="text-white-50" style="font-size:0.75rem">Fast shipping across Pakistan</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:rgba(249,115,22,0.15);color:#F97316;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:0.875rem">Cash on Delivery</h6>
                    <small class="text-white-50" style="font-size:0.75rem">Pay at your doorstep (COD)</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:rgba(249,115,22,0.15);color:#F97316;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:0.875rem">Quality Assurance</h6>
                    <small class="text-white-50" style="font-size:0.75rem">100% verified authentic goods</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:rgba(249,115,22,0.15);color:#F97316;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:0.875rem">WhatsApp Support</h6>
                    <small class="text-white-50" style="font-size:0.75rem">Fast help on order inquiries</small>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer mt-0">
    <div class="container">
        <div class="row g-4">
            {{-- Col 1: Brand & Contact Info --}}
            <div class="col-lg-3 col-md-6">
                <h5 class="d-inline-flex align-items-center gap-2 mb-3">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Capital<span style="color:#F97316">Cart</span><span class="small" style="font-size:0.75rem;opacity:0.85">.pk</span>
                </h5>
                <p class="small text-white-50 mb-3" style="line-height:1.6">
                    Islamabad’s Smart Shopping Destination &amp; B2B Wholesale Platform. Curating premium kitchen gadgets, home utilities, and smart lifestyle products across Pakistan.
                </p>
                <div class="small text-white-50 d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="15" height="15" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Islamabad, ICT, Pakistan</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <svg width="15" height="15" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('whatsapp_number', '+923002922584')) }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none">WhatsApp Helpline</a>
                    </div>
                </div>
            </div>

            {{-- Col 2: Categories (SEO Internal Links) --}}
            <div class="col-lg-2 col-6">
                <h5>Categories</h5>
                @foreach(\App\Models\Category::active()->orderBy('sort_order')->limit(6)->get() as $cat)
                    <a href="{{ route('shop', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('shop') }}" class="fw-semibold" style="color:#F97316">All Categories &rarr;</a>
            </div>

            {{-- Col 3: Customer Care & Policies --}}
            <div class="col-lg-2 col-6">
                <h5>Customer Care</h5>
                <a href="{{ route('ordering') }}">Ordering &amp; Payment</a>
                <a href="{{ route('ordering') }}#cod">Cash on Delivery (COD)</a>
                <a href="{{ route('privacy') }}">Privacy &amp; Data Policy</a>
                <a href="{{ route('cart') }}">Shopping Cart</a>
                <a href="{{ route('wishlist') }}">My Wishlist</a>
                <a href="{{ route('portfolio') }}#contact">Contact &amp; Support</a>
            </div>

            {{-- Col 4: Wholesale & Account --}}
            <div class="col-lg-2 col-6">
                <h5>B2B &amp; Wholesale</h5>
                <a href="{{ route('shop', ['onSale' => 1]) }}">Deals &amp; Clearance</a>
                <a href="{{ route('shop') }}?b2b=1">Bulk Wholesale Supply</a>
                <a href="{{ route('login') }}">Customer Sign In</a>
                <a href="{{ route('register') }}">Create Account</a>
                <a href="{{ route('portfolio') }}" class="fw-semibold" style="color:#F97316">★ About Developer</a>
            </div>

            {{-- Col 5: Newsletter & Badges --}}
            <div class="col-lg-3 col-6">
                <h5>Stay Updated</h5>
                <p class="small text-white-50 mb-3">Get exclusive wholesale prices &amp; flash deal alerts straight to your inbox.</p>
                <livewire:newsletter-box />
                <div class="pt-3">
                    <span class="d-block text-white-50 small mb-1" style="font-size:0.72rem">Accepted Payment Methods:</span>
                    <div class="d-flex flex-wrap gap-1.5 align-items-center">
                        <span class="badge bg-secondary-subtle text-light border border-secondary" style="font-size:0.65rem">Cash on Delivery</span>
                        <span class="badge bg-secondary-subtle text-light border border-secondary" style="font-size:0.65rem">JazzCash</span>
                        <span class="badge bg-secondary-subtle text-light border border-secondary" style="font-size:0.65rem">EasyPaisa</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO Entity Knowledge Graph & Regional Authority Text Block --}}
        <div class="footer-seo-content pt-4 mt-4 border-top border-secondary-subtle" style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="text-white-50 text-uppercase fw-bold mb-2" style="font-size:0.78rem;letter-spacing:0.05em">
                CapitalCart.pk — Online Shopping &amp; B2B Wholesale Destination in Pakistan
            </h6>
            <p class="text-white-50 small mb-2" style="line-height:1.65;font-size:0.75rem;">
                CapitalCart.pk is Pakistan’s leading online store and B2B wholesale platform based in Islamabad. We bring you curated daily essentials, viral kitchen gadgets, home improvement tools, bathroom accessories, and smart household organizers at unmatchable factory and wholesale rates. Enjoy seamless Cash on Delivery (COD) and fast shipping to Islamabad, Rawalpindi, Lahore, Karachi, Peshawar, Multan, Faisalabad, Gujranwala, Sialkot, Quetta, and across all cities in Pakistan.
            </p>
            <div class="footer-seo-tags d-flex flex-wrap gap-2 pt-1">
                <span class="badge bg-dark border border-secondary text-white-50" style="font-size:0.68rem">Kitchen Gadgets Pakistan</span>
                <span class="badge bg-dark border border-secondary text-white-50" style="font-size:0.68rem">Home Essentials Islamabad</span>
                <span class="badge bg-dark border border-secondary text-white-50" style="font-size:0.68rem">B2B Wholesale Online</span>
                <span class="badge bg-dark border border-secondary text-white-50" style="font-size:0.68rem">Cash on Delivery Store</span>
                <span class="badge bg-dark border border-secondary text-white-50" style="font-size:0.68rem">Household Utilities PK</span>
            </div>
        </div>

        <div class="footer-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>&copy; {{ date('Y') }} CapitalCart.pk. All rights reserved.</span>
            <span>Islamabad’s Smart Shopping Destination</span>
        </div>
    </div>
</footer>
@else
<footer class="py-4 text-center mt-0" style="background: #04070D; border-top: 1px solid rgba(0, 242, 254, 0.15); color: #94A3B8;">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <span class="small">&copy; {{ date('Y') }} Nasir Ali — Corporate NOC Engineer &amp; Software Developer.</span>
        <span class="small"><a href="{{ route('home') }}" class="text-decoration-none fw-semibold" style="color: #00F2FE;">&larr; Back to CapitalCart.pk Store</a> &bull; Islamabad, Pakistan</span>
    </div>
</footer>
@endif

{{-- Mobile Bottom Navigation Bar (Daraz-inspired) --}}
@if(!request()->routeIs('portfolio'))
<nav class="mobile-bottom-nav d-lg-none" aria-label="Mobile Navigation">
    <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Home</span>
    </a>
    <a href="{{ route('shop') }}" class="mobile-nav-item {{ request()->routeIs('shop') && !request('onSale') && !request('category') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
        </svg>
        <span>Categories</span>
    </a>
    <a href="{{ route('shop', ['onSale' => 1]) }}" class="mobile-nav-item {{ request()->boolean('onSale') ? 'active' : '' }}">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
        </svg>
        <span>Deals</span>
    </a>
    <a href="{{ route('cart') }}" class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-label="Shopping Cart">
        <div class="position-relative d-inline-block">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <span>Cart</span>
    </a>
    @if(auth()->check())
        @if(auth()->user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Admin</span>
            </a>
        @else
            <a href="{{ route('wishlist') }}" class="mobile-nav-item {{ request()->routeIs('wishlist') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span>Wishlist</span>
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" class="mobile-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Login</span>
        </a>
    @endif
</nav>
@endif

@livewireScripts
@stack('scripts')
</body>
</html>
