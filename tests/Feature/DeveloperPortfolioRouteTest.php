<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperPortfolioRouteTest extends TestCase
{
    use RefreshDatabase;
    public function test_portfolio_page_renders_successfully(): void
    {
        $response = $this->get(route('portfolio'));

        $response->assertStatus(200);
        $response->assertSee('Nasir Ali');
        $response->assertSee('Full-Stack Software Engineer');
        $response->assertSee('CapitalCart.pk Architecture');
        $response->assertSee('itsnasiralii');
    }
}
