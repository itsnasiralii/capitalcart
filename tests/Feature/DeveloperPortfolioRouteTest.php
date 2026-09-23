<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperPortfolioRouteTest extends TestCase
{
    use RefreshDatabase;
    public function test_portfolio_page_renders_successfully(): void
    {
        $this->seed(\Database\Seeders\PortfolioSeeder::class);

        $response = $this->get(route('portfolio'));

        $response->assertStatus(200);
        $response->assertSee('Nasir Ali');
        $response->assertSee('Network Engineer &amp; Software Developer', false);
        $response->assertSee('CapitalCart.pk');
        $response->assertSee('itsnasiralii');
    }
}
