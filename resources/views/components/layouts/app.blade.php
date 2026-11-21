<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} | CapitalCart.pk</title>
    <meta name="description" content="{{ $description ?? 'CapitalCart.pk — Islamabad’s Smart Shopping Destination. Quality products, fast delivery across Pakistan.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? config('app.name') }} | CapitalCart.pk">
    <meta property="og:description" content="{{ $description ?? 'CapitalCart.pk — Islamabad’s Smart Shopping Destination. Quality products, fast delivery across Pakistan.' }}">
    <meta property="og:image" content="{{ asset('images/hero-fallback.svg') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $title ?? config('app.name') }} | CapitalCart.pk">
    <meta name="twitter:description" content="{{ $description ?? 'CapitalCart.pk — Islamabad’s Smart Shopping Destination. Quality products, fast delivery across Pakistan.' }}">
    <meta name="twitter:image" content="{{ asset('images/hero-fallback.svg') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>
        /* Modern E-Commerce Layout Enhancements (Shopify / Dgenerationz style) */
        .navbar-marketory .navbar-nav {
            align-items: center;
            flex-wrap: nowrap;
        }
        .navbar-marketory .navbar-nav .nav-link {
            white-space: nowrap;
            padding: 0.45rem 0.85rem;
        }
        .navbar-marketory .dropdown-menu {
            background: #1E293B !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            box-shadow: 0 12px 35px rgba(0,0,0,0.35) !important;
        }
        .navbar-marketory .dropdown-item:hover {
            background: rgba(249, 115, 22, 0.15) !important;
            color: #F97316 !important;
        }
        /* Category Cards */
        .category-card {
            border-radius: 0.85rem !important;
            overflow: hidden !important;
            position: relative !important;
            height: 160px !important;
            background: #F1F5F9 !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
        }
        .category-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 25px rgba(0,0,0,0.15) !important;
        }
        .category-card img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.4s ease !important;
        }
        .category-card:hover img {
            transform: scale(1.06) !important;
        }
        .category-card .overlay {
            position: absolute !important;
            inset: 0 !important;
            background: linear-gradient(to top, rgba(15,23,42,0.88) 0%, rgba(15,23,42,0.3) 60%, transparent 100%) !important;
            display: flex !important;
            align-items: flex-end !important;
            padding: 0.85rem 1rem !important;
        }
        .category-card .overlay h5 {
            color: #fff !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            margin: 0 !important;
            font-size: 0.95rem !important;
            line-height: 1.3 !important;
        }
        /* Product Cards */
        .product-card {
            border: 1px solid #E5E7EB !important;
            border-radius: 0.75rem !important;
            overflow: hidden !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease !important;
            background: #fff !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .product-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 30px rgba(0,0,0,0.09) !important;
            border-color: #CBD5E1 !important;
        }
        .product-card .product-img-wrap {
            position: relative !important;
            overflow: hidden !important;
            aspect-ratio: 1 !important;
            background: #FFFFFF !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.35rem !important;
        }
        .product-card .product-img-wrap img {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            background: #FFFFFF !important;
            transition: transform 0.35s ease !important;
        }
        .product-card:hover .product-img-wrap img {
            transform: scale(1.05) !important;
        }
        .product-card .product-name {
            font-family: 'Poppins', sans-serif !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            color: #0F172A !important;
            line-height: 1.35 !important;
            margin-bottom: 0.45rem !important;
            min-height: 2.45rem !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }
        .product-card .product-category {
            font-size: 0.7rem !important;
            font-weight: 600 !important;
            color: #3B82F6 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.06em !important;
            margin-bottom: 0.25rem !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
    </style>
</head>
<body class="{{ request()->routeIs('portfolio') ? 'portfolio-mode' : '' }}" style="{{ request()->routeIs('portfolio') ? 'background-color: #070B14 !important; color: #E2E8F0 !important;' : '' }}">

{{-- Navbar --}}
<nav class="navbar navbar-marketory navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="{{ route('home') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span>Capital<span style="color:#F97316">Cart</span><span class="small" style="font-size:0.75rem;opacity:0.85">.pk</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto ms-3 gap-1 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shop') && !request('category') && !request('sort') ? 'active' : '' }}" href="{{ route('shop') }}">Shop</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-inline-flex align-items-center gap-1 {{ request('category') ? 'active' : '' }}" href="#" id="categoryNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0 py-2" aria-labelledby="categoryNavDropdown" style="background:#1E293B; max-height: 400px; overflow-y: auto; min-width: 250px; border-radius: 0.75rem;">
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
                    <a class="nav-link {{ request('sort') === 'featured' ? 'active' : '' }}" href="{{ route('shop', ['sort' => 'featured']) }}">Deals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('portfolio') ? 'active' : '' }}" href="{{ route('portfolio') }}">
                        <span class="badge rounded-pill me-1" style="background:#F97316;color:#fff;font-size:0.65rem">Dev</span>About Developer
                    </a>
                </li>
            </ul>
            <form action="{{ route('shop') }}" method="GET" class="d-none d-xl-flex me-3" style="max-width: 200px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-dark text-white border-secondary" placeholder="Search..." style="font-size:0.8rem; border-radius: 20px 0 0 20px;">
                    <button class="btn btn-outline-secondary text-white-50" type="submit" style="border-radius: 0 20px 20px 0; border-color: #475569;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

                {{-- Wishlist --}}
                @auth
                    <a href="{{ route('wishlist') }}" class="nav-link position-relative" title="Wishlist" style="color:rgba(255,255,255,0.8)">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </a>
                @endauth

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
<footer class="footer mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="d-inline-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Capital<span style="color:#F97316">Cart</span><span class="small" style="font-size:0.75rem;opacity:0.85">.pk</span>
                </h5>
                <p class="small" style="color:rgba(255,255,255,0.55)">Islamabad’s Smart Shopping Destination. Quality products, fast delivery, and exceptional service across Pakistan.</p>
            </div>
            <div class="col-lg-2 col-6">
                <h5>Shop</h5>
                @foreach(\App\Models\Category::active()->orderBy('sort_order')->limit(5)->get() as $cat)
                    <a href="{{ route('shop', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div class="col-lg-2 col-6">
                <h5>Account</h5>
                <a href="{{ route('login') }}">Sign In</a>
                <a href="{{ route('register') }}">Register</a>
                <a href="{{ route('cart') }}">Your Cart</a>
                <a href="{{ route('wishlist') }}">Wishlist</a>
                <a href="{{ route('privacy') }}">Privacy &amp; account settings</a>
                <a href="{{ route('ordering') }}">Ordering &amp; payments</a>
                <a href="{{ route('portfolio') }}" class="fw-semibold" style="color:#F97316">★ About Developer</a>
            </div>
            <div class="col-lg-4">
                <h5>Newsletter</h5>
                <p class="small" style="color:rgba(255,255,255,0.55)">Get exclusive deals and updates straight to your inbox.</p>
                <livewire:newsletter-box />
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

@livewireScripts
@stack('scripts')
</body>
</html>
