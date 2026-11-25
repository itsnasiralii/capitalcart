<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Livewire\Admin\Settings\StoreSettings;
use App\Livewire\Admin\Users\UserManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminStaffAndDeveloperToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_link_toggle_controls_storefront_visibility(): void
    {
        // 1. Enabled by default
        Setting::set('show_developer_link', '1', 'storefront');
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('About Dev');

        // 2. Disabled
        Setting::set('show_developer_link', '0', 'storefront');
        $responseDisabled = $this->get('/');
        $responseDisabled->assertStatus(200);
        $responseDisabled->assertDontSee('About Dev');

        // 3. Guest visiting /portfolio when disabled redirects to home
        $guestPortfolio = $this->get('/portfolio');
        $guestPortfolio->assertRedirect(route('home'));

        // 4. Admin visiting /portfolio when disabled is allowed
        $admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@capitalcart.pk',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $adminPortfolio = $this->actingAs($admin)->get('/portfolio');
        $adminPortfolio->assertStatus(200);
    }

    public function test_admin_settings_can_save_developer_link_toggle(): void
    {
        $admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@capitalcart.pk',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(StoreSettings::class)
            ->set('show_developer_link', false)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertEquals('0', Setting::get('show_developer_link'));
    }

    public function test_guest_and_non_admin_cannot_access_user_manager(): void
    {
        // Guest
        $this->get('/admin/users')->assertRedirect('/login');

        // Non-admin user
        $customer = User::create([
            'name'     => 'Customer',
            'email'    => 'customer@gmail.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $this->actingAs($customer)->get('/admin/users')->assertStatus(403);
    }

    public function test_admin_can_create_new_admin_credentials(): void
    {
        $admin = User::create([
            'name'     => 'Head Admin',
            'email'    => 'head@capitalcart.pk',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('openCreateModal')
            ->set('name', 'Second Admin')
            ->set('email', 'second@capitalcart.pk')
            ->set('password', 'SecretPass890')
            ->set('password_confirmation', 'SecretPass890')
            ->set('is_admin', true)
            ->call('saveUser')
            ->assertHasNoErrors();

        $newAdmin = User::where('email', 'second@capitalcart.pk')->first();
        $this->assertNotNull($newAdmin);
        $this->assertTrue($newAdmin->is_admin);
        $this->assertTrue(Hash::check('SecretPass890', $newAdmin->password));

        // Logout first so guest middleware allows login
        auth()->logout();

        // Test logging in with newly created admin credentials
        $loginResponse = $this->post('/login', [
            'email'    => 'second@capitalcart.pk',
            'password' => 'SecretPass890',
        ]);
        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($newAdmin);
    }

    public function test_admin_cannot_demote_or_delete_self(): void
    {
        $admin = User::create([
            'name'     => 'Head Admin',
            'email'    => 'head@capitalcart.pk',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        // Attempt self-demotion via edit modal
        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('openEditModal', $admin->id)
            ->set('is_admin', false)
            ->call('saveUser')
            ->assertHasErrors(['is_admin']);

        $this->assertTrue($admin->fresh()->is_admin);

        // Attempt self-deletion
        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->set('userToDeleteId', $admin->id)
            ->call('deleteUser');

        $this->assertNotNull(User::find($admin->id));
    }
}
