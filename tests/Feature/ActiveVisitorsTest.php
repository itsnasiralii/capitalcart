<?php

namespace Tests\Feature;

use App\Models\ActiveVisitor;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveVisitorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracks_session_with_sha256_hash(): void
    {
        $sessionId = 'mock-session-id-12345';
        $expectedHash = hash('sha256', $sessionId);

        ActiveVisitor::track($sessionId);

        $this->assertDatabaseHas('active_visitors', [
            'session_id' => $expectedHash,
        ]);
        $this->assertDatabaseMissing('active_visitors', [
            'session_id' => $sessionId,
        ]);
    }

    public function test_updates_existing_session_without_creating_duplicates(): void
    {
        $sessionId = 'unique-session-abc';

        ActiveVisitor::track($sessionId);
        ActiveVisitor::track($sessionId);

        $this->assertEquals(1, ActiveVisitor::count());
    }

    public function test_get_active_count_returns_within_window(): void
    {
        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-1'),
            'last_activity_at' => now()->subMinutes(2),
        ]);
        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-2'),
            'last_activity_at' => now()->subMinutes(3),
        ]);
        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-3'),
            'last_activity_at' => now()->subMinutes(12), // Out of 5-min window
        ]);

        $count = ActiveVisitor::getActiveCount(5);
        $this->assertEquals(2, $count);
    }

    public function test_prunes_expired_visitor_records(): void
    {
        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-active'),
            'last_activity_at' => now()->subMinutes(2),
        ]);
        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-expired'),
            'last_activity_at' => now()->subMinutes(15), // Expired past 10 min
        ]);

        $deleted = ActiveVisitor::pruneExpired(10);
        $this->assertEquals(1, $deleted);
        $this->assertEquals(1, ActiveVisitor::count());
    }

    public function test_storefront_displays_visitor_counter_when_enabled(): void
    {
        Setting::set('show_visitor_counter', '1', 'storefront');

        ActiveVisitor::create([
            'session_id'       => hash('sha256', 'session-active-1'),
            'last_activity_at' => now(),
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('online');
    }

    public function test_storefront_hides_visitor_counter_when_disabled(): void
    {
        Setting::set('show_visitor_counter', '0', 'storefront');

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('visitors currently shopping');
    }
}
