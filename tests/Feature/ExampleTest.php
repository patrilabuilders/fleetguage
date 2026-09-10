<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Standard Plan');
        $response->assertSee('Enterprise Plan');
        $response->assertSee('5 Active Users');
        $response->assertSee('10 Monitored Assets');
    }
}
