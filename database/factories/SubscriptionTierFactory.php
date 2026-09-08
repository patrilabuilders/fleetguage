<?php

namespace Database\Factories;

use App\Models\SubscriptionTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionTierFactory extends Factory
{
    protected $model = SubscriptionTier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' Tier',
            'max_users' => 5,
            'max_assets' => 10,
            'features' => ['reports' => true],
        ];
    }
}
