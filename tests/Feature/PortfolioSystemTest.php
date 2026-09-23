<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContactMessage;
use App\Models\PortfolioProject;
use App\Models\PortfolioExperience;
use App\Models\PortfolioSkill;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Portfolio\ContactForm;
use App\Livewire\Admin\Portfolio\PortfolioManager;

class PortfolioSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PortfolioSeeder::class);
    }

    public function test_public_portfolio_page_renders_with_identity_and_all_sections(): void
    {
        $response = $this->get(route('portfolio'));

        $response->assertStatus(200);
        $response->assertSee('Nasir Ali');
        $response->assertSee('Network Engineer & Software Developer');
        $response->assertSee('Zong CMPak');
        $response->assertDontSee('Amazon');
        $response->assertSee('Corporate NOC Engineer');
        $response->assertSee('Cybernet');
        $response->assertSee('Part-Time Software Developer');
        $response->assertSee('TCP/IP');
        $response->assertSee('Huawei NE40');
        $response->assertSee('Netmiko');
        $response->assertSee('CapitalCart.pk');
        $response->assertSee('View My Work');
        $response->assertSee('Download CV');
    }

    public function test_cv_download_route_returns_attachment(): void
    {
        $response = $this->get(route('portfolio.cv.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_contact_form_submits_and_stores_inquiry(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Hamza Khan')
            ->set('email', 'hamza@telecom-consulting.pk')
            ->set('subject', 'Enterprise Network Monitoring Project')
            ->set('message', 'We would like to consult on deploying automated BGP telemetry scripts.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('isSuccess', true)
            ->assertSee('Thank you, Nasir Ali has received your message');

        $this->assertDatabaseHas('contact_messages', [
            'name'    => 'Hamza Khan',
            'email'   => 'hamza@telecom-consulting.pk',
            'subject' => 'Enterprise Network Monitoring Project',
        ]);
    }

    public function test_contact_form_honeypot_silently_drops_spambots(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Bot Attacker')
            ->set('email', 'spambot@attack.com')
            ->set('subject', 'SEO promotion')
            ->set('message', 'Buy crypto now 100x return')
            ->set('honeypot', 'I am a malicious bot')
            ->call('submit')
            ->assertSet('isSuccess', true);

        $this->assertDatabaseMissing('contact_messages', [
            'name' => 'Bot Attacker',
        ]);
    }

    public function test_admin_portfolio_cms_requires_admin_privileges(): void
    {
        // 1. Guest redirected to login
        $this->get(route('admin.portfolio.index'))->assertRedirect(route('login'));

        // 2. Regular user gets 403 Forbidden
        $customer = User::create([
            'name'     => 'Regular Customer',
            'email'    => 'customer@example.com',
            'password' => bcrypt('secret123'),
            'is_admin' => false,
        ]);
        $this->actingAs($customer)->get(route('admin.portfolio.index'))->assertStatus(403);

        // 3. Admin gets 200 OK
        $admin = User::create([
            'name'     => 'Nasir Ali',
            'email'    => 'admin@capitalcart.pk',
            'password' => bcrypt('NasirAli@123'),
            'is_admin' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.portfolio.index'))->assertStatus(200);
    }

    public function test_admin_can_update_portfolio_profile_settings(): void
    {
        $admin = User::create([
            'name'     => 'Nasir Ali',
            'email'    => 'admin@capitalcart.pk',
            'password' => bcrypt('NasirAli@123'),
            'is_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(PortfolioManager::class)
            ->set('name', 'Engr. Nasir Ali')
            ->set('title', 'Lead Network Engineer & Solutions Architect')
            ->set('intro', 'Updated intro text for testing purposes with adequate length.')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertEquals('Engr. Nasir Ali', setting('portfolio_name'));
        $this->assertEquals('Lead Network Engineer & Solutions Architect', setting('portfolio_title'));
    }
}
