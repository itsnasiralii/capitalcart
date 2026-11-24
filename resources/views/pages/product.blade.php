<x-layouts.app :title="$product->name . ' — Buy Online in Pakistan'" :description="Str::limit(strip_tags($product->short_description ?? $product->description ?? ('Buy ' . $product->name . ' at best price in Pakistan from CapitalCart.pk with fast home delivery and cash on delivery.')), 155)">

    <div class="py-3" style="background:#fff;border-bottom:1px solid #E5E7EB">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop') }}">Shop</a></li>
                    @if($product->category)
                        <li class="breadcrumb-item"><a href="{{ route('shop', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ Str::limit($product->name, 40) }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-5">
        <livewire:shop.product-detail :slug="$product->slug" />
    </div>

    @push('scripts')
    @php
        $productSchema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => [
                $product->primary_image_url
            ],
            'description' => strip_tags($product->short_description ?? $product->description ?? $product->name),
            'sku' => $product->sku ?? ('SKU-' . $product->id),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'CapitalCart',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $product->slug),
                'priceCurrency' => 'PKR',
                'price' => (string) $product->effective_price,
                'availability' => $product->is_in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'CapitalCart.pk',
                ],
            ],
        ];

        if ($product->reviews->count() > 0) {
            $productSchema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) round($product->average_rating, 1),
                'reviewCount' => (string) $product->reviews->count(),
            ];
        }

        $breadcrumbs = [
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
        ];

        if ($product->category) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $product->category->name,
                'item' => route('shop', ['category' => $product->category->slug]),
            ];
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $product->name,
                'item' => route('product.show', $product->slug),
            ];
        } else {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $product->name,
                'item' => route('product.show', $product->slug),
            ];
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs,
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endpush

</x-layouts.app>
