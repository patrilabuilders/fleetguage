<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SubscriptionTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'subscription_tier_id' => SubscriptionTier::factory(),
            'status' => 'active',
        ];
    }
}
