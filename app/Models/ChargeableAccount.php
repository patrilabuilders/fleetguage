<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeableAccount extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = ['company_id', 'name', 'classification', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleted(function (ChargeableAccount $account) {
            $account->subAccounts()->delete();
        });

        static::restored(function (ChargeableAccount $account) {
            $account->subAccounts()->withTrashed()->restore();
        });
    }

    public function subAccounts(): HasMany
    {
        return $this->hasMany(SubAccount::class);
    }

    public function budgets(): HasManyThrough
    {
        return $this->hasManyThrough(SubAccountBudget::class, SubAccount::class);
    }
}
