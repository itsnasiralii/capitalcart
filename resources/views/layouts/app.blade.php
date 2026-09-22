<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} | CapitalCart.pk</title>
    <meta name="description" content="{{ $description ?? 'CapitalCart.pk — Islamabad’s Smart Shopping Destination. Quality products, fast delivery across Pakistan.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>

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
            <ul class="navbar-nav me-auto ms-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shop') ? 'active' : '' }}" href="{{ route('shop') }}">Shop</a>
                </li>
                @foreach(\App\Models\Category::active()->orderBy('sort_order')->limit(4)->get() as $cat)
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('shop', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                </li>
                @endforeach
            </ul>
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

@livewireScripts
</body>
</html>
