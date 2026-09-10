<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\Company;
use App\Models\SubscriptionTier;
use App\Models\User;
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
}
