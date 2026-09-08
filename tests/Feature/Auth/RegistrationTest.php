<?php

namespace Tests\Feature\Auth;

use App\Models\SubscriptionTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Register Your Fleet');
    }

    public function test_new_users_can_register(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $tier = SubscriptionTier::create([
            'name' => 'Standard',
            'max_users' => 5,
            'max_assets' => 10,
            'features' => ['reports' => false],
        ]);

        $response = $this->post('/register', [
            'company_name' => 'Acme Fleet Corp',
            'subscription_tier_id' => $tier->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // Assert company was created
        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Fleet Corp',
            'subscription_tier_id' => $tier->id,
        ]);

        // Assert user belongs to company and has the role administrator
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('administrator', $user->role);
        $this->assertNotNull($user->company_id);

        // Assert default asset types are seeded for this company
        $this->assertDatabaseHas('asset_types', [
            'company_id' => $user->company_id,
            'name' => 'Excavator',
        ]);
    }
}
