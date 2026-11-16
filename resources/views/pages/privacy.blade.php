<x-layouts.app title="Privacy &amp; account settings">
    <article class="container py-5" style="max-width: 48rem">
        <h1>Privacy &amp; account settings</h1>
        <p>Capital Cart uses your session to keep your cart and sign you in. When you order, we store your contact details, delivery details, purchased items and payment status to process the order. On WhatsApp checkout, opening the WhatsApp link shares the order details with the seller through WhatsApp. Card payments, when available, use Stripe.</p>
        <p>The newsletter stores the email you submit. The contact form stores your message and contact details. These forms also record an IP address. This site loads fonts from Google Fonts.</p>
        <p>For questions about your information, newsletter removal or past orders, <a href="{{ route('portfolio') }}#contact">contact Capital Cart</a>. Include the order reference if relevant.</p>
        @if(session('account_message')) <p class="alert alert-success" role="status">{{ session('account_message') }}</p> @endif
        @if($errors->any()) <p class="alert alert-danger" role="alert">{{ $errors->first() }}</p> @endif
        <h2 class="h4 mt-4">Optional visitor count</h2>
        <p>You can let the store count this browser session in its recent visitor total. This uses a hashed session identifier. The preference lasts for this session. We respect Do Not Track and Global Privacy Control.</p>
        <form method="post" action="{{ route('privacy.visitors') }}">
            @csrf
            <input type="hidden" name="allow" value="0">
            <label><input type="checkbox" name="allow" value="1" @checked(session('allow_visitor_count'))> Include this session in the visitor count</label>
            <button class="btn btn-outline-primary btn-sm" type="submit">Save preference</button>
        </form>
        @auth
            <h2 class="h4 mt-4">Change password</h2>
            <form method="post" action="{{ route('account.password') }}">
                @csrf @method('PUT')
                <label class="d-block mb-2">Current password<input class="form-control" type="password" name="current_password" autocomplete="current-password" required></label>
                <label class="d-block mb-2">New password (at least 12 characters)<input class="form-control" type="password" name="password" autocomplete="new-password" minlength="12" required></label>
                <label class="d-block mb-2">Confirm new password<input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required></label>
                <button class="btn btn-primary" type="submit">Change password</button>
            </form>
            @unless(auth()->user()->is_admin)
                <h2 class="h4 mt-4">Delete my customer account</h2>
                <p>Deletion removes your login, wishlist and reviews. We keep order and payment records separately for fulfillment and accounting. Contact us about any information you submitted separately through the newsletter or contact form.</p>
                <form method="post" action="{{ route('account.destroy') }}">
                    @csrf @method('DELETE')
                    <label class="d-block mb-2">Current password<input class="form-control" type="password" name="current_password" autocomplete="current-password" required></label>
                    <label class="d-block mb-2"><input type="checkbox" name="confirm_delete" value="1" required> I understand that deletion is permanent.</label>
                    <button class="btn btn-outline-danger" type="submit">Delete my account</button>
                </form>
            @endunless
        @endauth
    </article>
</x-layouts.app>
