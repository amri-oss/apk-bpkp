<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    public function test_landing_page_fills_the_viewport_without_a_tall_image_overflow(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('min-h-screen flex flex-col', false)
            ->assertSee('flex-1 min-h-0', false)
            ->assertSee('h-full w-full object-cover', false);
    }
}
