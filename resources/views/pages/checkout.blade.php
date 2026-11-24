<x-layouts.app title="Checkout" robots="noindex, follow">

    <div class="py-3" style="background:#fff;border-bottom:1px solid #E5E7EB">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart') }}">Cart</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
            <h1 class="font-poppins fw-bold mb-0 mt-2" style="font-size:1.75rem">Checkout</h1>
        </div>
    </div>

    <div class="container py-5">
        <livewire:checkout.checkout-form />
    </div>

</x-layouts.app>
