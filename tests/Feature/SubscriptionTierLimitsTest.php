<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\Company;
use App\Models\SubAccount;
use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubscriptionTierLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_max_classifications_limit_is_enforced()
    {
        $tier = SubscriptionTier::create([
            'name' => 'Custom Limited Tier',
            'max_users' => 5,
            'max_assets' => 10,
            'max_classifications' => 2,
            'max_accounts' => 5,
            'max_sub_accounts_per_account' => 5,
            'features' => ['reports' => false],
        ]);

        $company = Company::create([
            'name' => 'Limited Corp',
            'subscription_tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'administrator',
        ]);

        // Log in
        $this->actingAs($user);

        // Create first asset type (classification)
        AssetType::create([
            'company_id' => $company->id,
            'name' => 'Type A',
        ]);

        // Create second asset type
        AssetType::create([
            'company_id' => $company->id,
            'name' => 'Type B',
        ]);

        // Attempting to create a third asset type should throw ValidationException
        $this->expectException(ValidationException::class);

        AssetType::create([
            'company_id' => $company->id,
            'name' => 'Type C',
        ]);
    }

    public function test_max_accounts_limit_is_enforced()
    {
        $tier = SubscriptionTier::create([
            'name' => 'Custom Limited Tier 2',
            'max_users' => 5,
            'max_assets' => 10,
            'max_classifications' => 5,
            'max_accounts' => 2,
            'max_sub_accounts_per_account' => 5,
            'features' => ['reports' => false],
        ]);

        $company = Company::create([
            'name' => 'Limited Corp 2',
            'subscription_tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'administrator',
        ]);

        // Log in
        $this->actingAs($user);

        // Create first chargeable account
        ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account A',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Create second chargeable account
        ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account B',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Attempting to create a third chargeable account should throw ValidationException
        $this->expectException(ValidationException::class);

        ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account C',
            'classification' => 'Running',
            'status' => 'Active',
        ]);
    }

    public function test_max_sub_accounts_per_account_limit_is_enforced()
    {
        $tier = SubscriptionTier::create([
            'name' => 'Custom Limited Tier 3',
            'max_users' => 5,
            'max_assets' => 10,
            'max_classifications' => 5,
            'max_accounts' => 5,
            'max_sub_accounts_per_account' => 2,
            'features' => ['reports' => false],
        ]);

        $company = Company::create([
            'name' => 'Limited Corp 3',
            'subscription_tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'administrator',
        ]);

        // Log in
        $this->actingAs($user);

        // Create a chargeable account
        $account = ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account X',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Create first sub-account
        $account->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub X1',
        ]);

        // Create second sub-account
        $account->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub X2',
        ]);

        // Attempting to create a third sub-account under same account should throw ValidationException
        $this->expectException(ValidationException::class);

        $account->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub X3',
        ]);
    }

    public function test_max_sub_accounts_per_account_limit_is_independent_per_account()
    {
        $tier = SubscriptionTier::create([
            'name' => 'Custom Limited Tier 4',
            'max_users' => 5,
            'max_assets' => 10,
            'max_classifications' => 5,
            'max_accounts' => 5,
            'max_sub_accounts_per_account' => 2,
            'features' => ['reports' => false],
        ]);

        $company = Company::create([
            'name' => 'Limited Corp 4',
            'subscription_tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'administrator',
        ]);

        // Log in
        $this->actingAs($user);

        // Create first chargeable account
        $account1 = ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account X',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Create second chargeable account
        $account2 = ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Account Y',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        // Create 2 sub-accounts under Account X (reaches limit)
        $account1->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub X1',
        ]);
        $account1->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub X2',
        ]);

        // Create a sub-account under Account Y (should succeed!)
        $subY = $account2->subAccounts()->create([
            'company_id' => $company->id,
            'name' => 'Sub Y1',
        ]);

        $this->assertDatabaseHas('sub_accounts', [
            'id' => $subY->id,
            'name' => 'Sub Y1',
        ]);
    }

    public function test_infinite_limits_are_not_enforced()
    {
        $tier = SubscriptionTier::create([
            'name' => 'Infinite Tier',
            'max_users' => null,
            'max_assets' => null,
            'max_classifications' => null,
            'max_accounts' => null,
            'max_sub_accounts_per_account' => null,
            'features' => ['reports' => true],
        ]);

        $company = Company::create([
            'name' => 'Unlimited Corp',
            'subscription_tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'administrator',
        ]);

        $this->actingAs($user);

        // We can create multiple users beyond any low default limit (e.g. 10)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'company_id' => $company->id,
                'name' => "User {$i}",
                'email' => "user{$i}@unlimited.com",
                'password' => bcrypt('password'),
                'role' => 'data_logger',
            ]);
        }

        // We can create multiple classifications (asset types) beyond limits
        for ($i = 1; $i <= 10; $i++) {
            AssetType::create([
                'company_id' => $company->id,
                'name' => "Type {$i}",
            ]);
        }

        $typeBackhoe = AssetType::where('company_id', $company->id)->first();

        // We can create multiple assets beyond limits
        for ($i = 1; $i <= 15; $i++) {
            Asset::create([
                'company_id' => $company->id,
                'asset_type_id' => $typeBackhoe->id,
                'fleet_no' => "Fleet-{$i}",
                'plate_no' => "Plate-{$i}",
                'status' => 'active',
                'tank_capacity' => 100,
                'last_kilometer_reading' => 0,
                'last_engine_hours' => 0,
                'fuel_type' => 'Diesel',
            ]);
        }

        // We can create multiple chargeable accounts beyond limits
        $account = null;
        for ($i = 1; $i <= 12; $i++) {
            $account = ChargeableAccount::create([
                'company_id' => $company->id,
                'name' => "Account {$i}",
                'classification' => 'Running',
                'status' => 'Active',
            ]);
        }

        // We can create multiple sub-accounts under one account beyond limits
        for ($i = 1; $i <= 12; $i++) {
            $account->subAccounts()->create([
                'company_id' => $company->id,
                'name' => "Sub {$i}",
            ]);
        }

        // Assert all were successfully created
        $this->assertEquals(11, User::where('company_id', $company->id)->count()); // 1 admin + 10 data loggers
        $this->assertEquals(10, AssetType::where('company_id', $company->id)->count());
        $this->assertEquals(15, Asset::where('company_id', $company->id)->count());
        $this->assertEquals(12, ChargeableAccount::where('company_id', $company->id)->count());
        $this->assertEquals(12, SubAccount::where('chargeable_account_id', $account->id)->count());
    }

    public function test_admin_can_create_and_update_tiers_with_infinite_limits()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@fleetguage.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        // Create tier with infinite limits and is_free and custom badge text
        $response = $this->post(route('super-admin.tiers.store'), [
            'name' => 'Super Infinite Tier',
            'is_free' => '1',
            'description' => 'Completely Free and Unlimited!',
            'badge_text' => 'Completely Free',
            'unlimited_max_users' => '1',
            'unlimited_max_assets' => '1',
            'unlimited_max_classifications' => '1',
            'unlimited_max_accounts' => '1',
            'unlimited_max_sub_accounts_per_account' => '1',
            'reports_enabled' => '1',
        ]);

        $response->assertRedirect(route('super-admin.dashboard'));
        $this->assertDatabaseHas('subscription_tiers', [
            'name' => 'Super Infinite Tier',
            'price' => 0.00,
            'description' => 'Completely Free and Unlimited!',
            'badge_text' => 'Completely Free',
            'max_users' => null,
            'max_assets' => null,
            'max_classifications' => null,
            'max_accounts' => null,
            'max_sub_accounts_per_account' => null,
        ]);

        $tier = SubscriptionTier::where('name', 'Super Infinite Tier')->first();

        // Update tier back to finite limits, custom price, name, description, badge text, and then to a mix of both
        $response2 = $this->patch(route('super-admin.tiers.update', $tier->id), [
            'name' => 'Super Renamed Tier',
            'price' => 19.99,
            'description' => 'A beautifully updated custom tier.',
            'badge_text' => 'Super Premium',
            'max_users' => 20,
            'unlimited_max_assets' => '1',
            'max_classifications' => 15,
            'unlimited_max_accounts' => '1',
            'max_sub_accounts_per_account' => 30,
            'reports_enabled' => '1',
        ]);

        $response2->assertRedirect(route('super-admin.dashboard'));
        $this->assertDatabaseHas('subscription_tiers', [
            'id' => $tier->id,
            'name' => 'Super Renamed Tier',
            'price' => 19.99,
            'description' => 'A beautifully updated custom tier.',
            'badge_text' => 'Super Premium',
            'max_users' => 20,
            'max_assets' => null,
            'max_classifications' => 15,
            'max_accounts' => null,
            'max_sub_accounts_per_account' => 30,
        ]);
    }
}
