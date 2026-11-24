<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_has_a_formal_summary_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withoutVite()
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('page-banner', false)
            ->assertSee('Ringkasan Dashboard')
            ->assertSee('Total Audiens')
            ->assertSee('Konsultasi Terbaru')
            ->assertSee('Ringkasan Status')
            ->assertSee('bg-slate-50', false)
            ->assertSee('max-h-[260px] overflow-y-auto', false);
    }
}
