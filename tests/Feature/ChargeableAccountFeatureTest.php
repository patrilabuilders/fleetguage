<?php

namespace Tests\Feature;

use App\Models\ChargeableAccount;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeableAccountFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_chargeable_accounts_routes()
    {
        $user = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.create'));
        $response->assertStatus(200);
    }

    public function test_chargeable_accounts_index_displays_sub_account_count()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create([
            'name' => 'Project Gamma',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $account->subAccounts()->create(['name' => 'Sub Account 1']);
        $account->subAccounts()->create(['name' => 'Sub Account 2']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);
        $response->assertSee('Sub-Account');
        $response->assertSee('2');
    }

    public function test_chargeable_accounts_index_has_alpine_sorting_searching_and_toggle()
    {
        $user = User::factory()->create(['role' => 'administrator']);

        $activeAccount = ChargeableAccount::create([
            'name' => 'Active Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $inactiveAccount = ChargeableAccount::create([
            'name' => 'Inactive Project Beta',
            'classification' => 'Running',
            'status' => 'Inactive',
        ]);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(200);

        $response->assertSee('x-model="search"', false);
        $response->assertSee('x-model="showInactive"', false);

        $response->assertSee("@click=\"sort('account')\"", false);
        $response->assertSee("@click=\"sort('type')\"", false);
        $response->assertSee("@click=\"sort('sub-account')\"", false);
        $response->assertSee("@click=\"sort('status')\"", false);

        $response->assertSee('data-account="active project alpha"', false);
        $response->assertSee('data-account="inactive project beta"', false);
        $response->assertSee('data-status="active"', false);
        $response->assertSee('data-status="inactive"', false);
    }

    public function test_standard_user_cannot_access_chargeable_accounts_routes()
    {
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_create_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);

        // Test creating Running account
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Test creating Scoped account
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Beta',
            'classification' => 'Scoped',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'name' => 'Project Beta',
            'classification' => 'Scoped',
            'start_date' => now()->format('Y-m-d').' 00:00:00',
            'end_date' => now()->addMonth()->format('Y-m-d').' 00:00:00',
        ]);
    }

    public function test_scoped_account_requires_dates()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project Gamma',
            'classification' => 'Scoped',
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_administrator_can_update_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Old Name', 'classification' => 'Running', 'status' => 'Active']);

        $response = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'New Name',
            'classification' => 'Scoped',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'Inactive',
        ]);

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertDatabaseHas('chargeable_accounts', [
            'id' => $account->id,
            'name' => 'New Name',
            'classification' => 'Scoped',
            'start_date' => '2026-01-01 00:00:00',
            'end_date' => '2026-12-31 00:00:00',
            'status' => 'Inactive',
        ]);
    }

    public function test_chargeable_account_must_have_unique_name()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        ChargeableAccount::create(['name' => 'Existing Account', 'status' => 'Active']);

        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Existing Account',
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertEquals(1, ChargeableAccount::count());
    }

    public function test_administrator_can_soft_delete_chargeable_account()
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($user)->delete(route('chargeable-accounts.destroy', $account));

        $response->assertRedirect(route('chargeable-accounts.index'));
        $this->assertSoftDeleted('chargeable_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_chargeable_accounts_show_page_displays_sub_accounts_budgets(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Main Account', 'status' => 'Active', 'classification' => 'Running']);

        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        // Create an Approved budget
        $subAccount->budgets()->create([
            'budget_quantity' => 1250.00,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        // Create a Pending budget
        $subAccount->budgets()->create([
            'budget_quantity' => 350.50,
            'status' => 'Pending',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.show', $account));

        $response->assertStatus(200);
        $response->assertSee('Sub Alpha');
        $response->assertSee('1,250.00 L');
        $response->assertSee('350.50 L');
    }

    public function test_chargeable_accounts_index_page_displays_total_budgets(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'Consolidated Account', 'status' => 'Active', 'classification' => 'Running']);

        $subAccount1 = $account->subAccounts()->create(['name' => 'Sub One']);
        $subAccount2 = $account->subAccounts()->create(['name' => 'Sub Two']);

        // Approved budgets on SubOne and SubTwo
        $subAccount1->budgets()->create([
            'budget_quantity' => 2000.00,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);
        $subAccount2->budgets()->create([
            'budget_quantity' => 1500.50,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        // Pending budget on SubOne
        $subAccount1->budgets()->create([
            'budget_quantity' => 450.25,
            'status' => 'Pending',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Total Approved Budget');
        $response->assertSee('Total Pending Budget');

        // Combined approved budget = 2000.00 + 1500.50 = 3500.50 L
        $response->assertSee('3,500.50 L');

        // Combined pending budget = 450.25 L
        $response->assertSee('450.25 L');
    }

    public function test_chargeable_account_print_route_renders_correctly(): void
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($user);

        $account = ChargeableAccount::create([
            'name' => 'Print Test Account',
            'status' => 'Active',
            'classification' => 'Running'
        ]);

        $subAccount = $account->subAccounts()->create(['name' => 'Sub Printable']);

        $subAccount->budgets()->create([
            'budget_quantity' => 1250.00,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        $subAccount->budgets()->create([
            'budget_quantity' => 350.50,
            'status' => 'Pending',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('chargeable-accounts.print', $account));

        $response->assertStatus(200);
        $response->assertSee('Print Test Account');
        $response->assertSee('Sub Printable');
        $response->assertSee('1,250.00 L');
        $response->assertSee('350.50 L');
        $response->assertSee('Sub-Account Breakdown');
        $response->assertSee('Print Date');
        $response->assertSee(now()->format('M d, Y'));
        $response->assertDontSee('Account Information');
    }

    public function test_chargeable_account_name_cannot_contain_colons(): void
    {
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $user = User::factory()->create(['role' => 'administrator']);

        // Create with colon - should fail validation
        $response = $this->actingAs($user)->post(route('chargeable-accounts.store'), [
            'name' => 'Project: Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $response->assertSessionHasErrors('name');

        // Update with colon - should fail validation
        $account = ChargeableAccount::create(['name' => 'Valid Project', 'status' => 'Active', 'classification' => 'Running']);
        $response2 = $this->actingAs($user)->patch(route('chargeable-accounts.update', $account), [
            'name' => 'Valid: Project',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $response2->assertSessionHasErrors('name');
    }
}
