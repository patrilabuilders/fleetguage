<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_user_creation_pages()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->get(route('users.create-data-logger'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('users.create-moderator'));
        $response->assertStatus(200);
    }

    public function test_moderator_cannot_access_data_logger_creation_page()
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->get(route('users.create-data-logger'));
        $response->assertStatus(403);
    }

    public function test_moderator_cannot_access_moderator_creation_page()
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->get(route('users.create-moderator'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_store_moderator()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'role' => 'moderator',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'mod@example.com', 'role' => 'moderator']);
    }

    public function test_moderator_cannot_store_moderator()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->post(route('users.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'role' => 'moderator',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'mod@example.com']);
    }

    public function test_moderator_cannot_store_data_logger()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]); // Disable CSRF middleware to bypass CSRF issues in this test environment
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->post(route('users.store'), [
            'name' => 'New Data Logger',
            'email' => 'dl@example.com',
            'role' => 'data_logger',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'dl@example.com']);
    }

    public function test_administrator_can_update_user_role()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'administrator']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'data_logger']);

        $response = $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'moderator',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'updated@example.com', 'role' => 'moderator']);
    }

    public function test_moderator_cannot_update_user_role()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $company = Company::factory()->create();
        $moderator = User::factory()->create(['company_id' => $company->id, 'role' => 'moderator']);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'data_logger']);

        $response = $this->actingAs($moderator)->patch(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'administrator',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'updated@example.com']);
    }

    public function test_administrator_can_access_settings_index()
    {
        $admin = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($admin)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Users');
        $response->assertSee('Classifications');
    }

    public function test_moderator_cannot_access_settings_index()
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->get(route('settings.index'));
        $response->assertStatus(403);
    }
}
