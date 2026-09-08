<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccomplishmentRegistry extends Model
{
    use HasFactory, Tenantable;

    protected $table = 'accomplishment_registry';

    protected $fillable = [
        'company_id',
        'sub_account_id',
        'quantity',
        'date_at',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'float',
        'date_at' => 'date',
    ];

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class);
    }
}
