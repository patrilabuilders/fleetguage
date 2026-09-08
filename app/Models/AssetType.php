<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = ['company_id', 'name'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
