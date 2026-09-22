<?php

namespace Tests\Feature;

use App\Livewire\NewsletterBox;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribes_valid_email_successfully(): void
    {
        Livewire::test(NewsletterBox::class)
            ->set('email', 'customer@capitalcart.pk')
            ->call('subscribe')
            ->assertSet('statusType', 'success')
            ->assertSee('Thank you for subscribing to CapitalCart');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email'  => 'customer@capitalcart.pk',
            'status' => 'active',
        ]);
    }

    public function test_handles_duplicate_email_gracefully(): void
    {
        NewsletterSubscriber::create([
            'email'  => 'existing@capitalcart.pk',
            'status' => 'active',
        ]);

        Livewire::test(NewsletterBox::class)
            ->set('email', 'existing@capitalcart.pk')
            ->call('subscribe')
            ->assertSet('statusType', 'info')
            ->assertSee('already subscribed');

        $this->assertEquals(1, NewsletterSubscriber::where('email', 'existing@capitalcart.pk')->count());
    }

    public function test_rejects_invalid_email_format(): void
    {
        Livewire::test(NewsletterBox::class)
            ->set('email', 'not-an-email')
            ->call('subscribe')
            ->assertHasErrors(['email']);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_honeypot_field_silently_drops_spambots(): void
    {
        Livewire::test(NewsletterBox::class)
            ->set('email', 'spammer@botnet.com')
            ->set('honeypot', 'http://spam-site.com') // Honeypot trap filled
            ->call('subscribe')
            ->assertSet('statusType', 'success');

        // Database should NOT contain the spam email
        $this->assertDatabaseMissing('newsletter_subscribers', [
            'email' => 'spammer@botnet.com',
        ]);
    }
}
