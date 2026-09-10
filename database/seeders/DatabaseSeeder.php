<?php

namespace Database\Seeders;

use App\Models\AssetType;
use App\Models\Company;
use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Subscription Tiers
        $standard = SubscriptionTier::create([
            'name' => 'Standard',
            'max_users' => 5,
            'max_assets' => 10,
            'max_classifications' => 5,
            'max_accounts' => 10,
            'max_sub_accounts_per_account' => 10,
            'features' => ['reports' => false],
        ]);

        $enterprise = SubscriptionTier::create([
            'name' => 'Enterprise',
            'max_users' => 100,
            'max_assets' => 1000,
            'max_classifications' => 100,
            'max_accounts' => 200,
            'max_sub_accounts_per_account' => 500,
            'features' => ['reports' => true],
        ]);

        // 3. Create a Default/Acme Company
        $company = Company::create([
            'name' => 'Acme Fuel Corp',
            'subscription_tier_id' => $enterprise->id,
            'status' => 'active',
        ]);

        // 4. Create Initial Administrator associated with the default Company
        User::create([
            'company_id' => $company->id,
            'name' => 'Company Administrator',
            'email' => 'admin@fuel.com',
            'password' => Hash::make('password'),
            'role' => 'administrator',
            'is_temporary_password' => false,
        ]);

        // 5. Sample Asset Types associated with the default Company
        AssetType::create(['company_id' => $company->id, 'name' => 'Sedan']);
        AssetType::create(['company_id' => $company->id, 'name' => 'SUV']);
        AssetType::create(['company_id' => $company->id, 'name' => 'Truck']);
        AssetType::create(['company_id' => $company->id, 'name' => 'Van']);
        AssetType::create(['company_id' => $company->id, 'name' => 'Excavator']);
    }
}
