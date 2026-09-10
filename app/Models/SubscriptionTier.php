<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_users',
        'max_assets',
        'max_classifications',
        'max_accounts',
        'max_sub_accounts_per_account',
        'features',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'max_users' => 'integer',
            'max_assets' => 'integer',
            'max_classifications' => 'integer',
            'max_accounts' => 'integer',
            'max_sub_accounts_per_account' => 'integer',
        ];
    }

    /**
     * Get the companies subscribed to this tier.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
