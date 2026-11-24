<?php

namespace Tests\Feature;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationComposerLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_composer_is_in_a_viewport_bounded_layout_for_both_participants(): void
    {
        $audiens = User::factory()->create(['role' => 'audiens']);
        $konsultan = User::factory()->create(['role' => 'konsultan']);
        $konsultasi = Konsultasi::create([
            'audiens_id' => $audiens->id,
            'konsultan_id' => $konsultan->id,
            'status' => 'berlangsung',
        ]);

        $audienResponse = $this->actingAs($audiens)
            ->withoutVite()
            ->get(route('audiens.konsultasi-saya', $konsultasi));

        $audienResponse->assertOk()
            ->assertSee('chat-layout', false)
            ->assertSee('chat-page', false)
            ->assertSee('id="input-pesan"', false)
            ->assertDontSee('min-height: 78vh', false);

        $fullChatResponse = $this->actingAs($audiens)
            ->withoutVite()
            ->get(route('konsultasi.show', $konsultasi));

        $fullChatResponse->assertOk()
            ->assertSee('chat-layout', false)
            ->assertSee('chat-page', false)
            ->assertSee('id="input-pesan"', false)
            ->assertDontSee('min-height: 78vh', false);

        $konsultanResponse = $this->actingAs($konsultan)
            ->withoutVite()
            ->get(route('konsultan.obrolan', $konsultasi));

        $konsultanResponse->assertOk()
            ->assertSee('chat-layout', false)
            ->assertSee('chat-page', false)
            ->assertSee('id="input-pesan"', false)
            ->assertDontSee('height: 70vh', false);
    }
}
