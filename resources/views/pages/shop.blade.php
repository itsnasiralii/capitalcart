<x-layouts.app title="Shop All Products — Kitchen, Home &amp; Daily Essentials" description="Explore trending household essentials, kitchen tools, and smart gadgets at CapitalCart.pk. Fast delivery, wholesale discounts, and cash on delivery across Pakistan.">

    <div class="py-4" style="background:#fff;border-bottom:1px solid #E5E7EB">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Shop</li>
                </ol>
            </nav>
            <h1 class="font-poppins fw-bold mb-0" style="font-size:1.75rem">All Products</h1>
        </div>
    </div>

    <div class="container py-5">
        <livewire:shop.product-catalog />
    </div>

    @push('scripts')
    @php
        $shopBreadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Shop',
                    'item' => route('shop'),
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($shopBreadcrumbs, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endpush

</x-layouts.app>
