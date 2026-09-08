<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class SubAccount extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id',
        'chargeable_account_id',
        'name',
        'merged_to_id',
        'merged_by',
        'merged_at',
        'merge_remarks',
        'type',
        'quantity',
        'unit',
    ];

    protected $appends = [
        'display_name',
    ];

    protected $casts = [
        'merged_at' => 'datetime',
        'quantity' => 'float',
    ];

    public function accomplishments(): HasMany
    {
        return $this->hasMany(AccomplishmentRegistry::class);
    }

    public function getAccomplishmentAttribute(): float
    {
        if ($this->quantity && $this->quantity > 0) {
            $latestAccomplishment = $this->accomplishments()->latest('date_at')->latest('id')->first();

            if ($latestAccomplishment) {
                return ($latestAccomplishment->quantity / $this->quantity) * 100;
            }
        }

        return 0.0;
    }

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(SubAccountBudget::class);
    }

    public function utilizationEntries(): HasMany
    {
        return $this->hasMany(UtilizationEntry::class);
    }

    public function mergedTo(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'merged_to_id')->withTrashed();
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    public function consumedBudget(): float
    {
        // 1. Asset-based consumption via utilization entries attached to DONE fuel orders
        $entries = $this->utilizationEntries()
            ->whereHas('fuelOrder', function ($q) {
                $q->where('status', 'DONE');
            })
            ->get();

        $assetConsumed = 0;
        foreach ($entries as $entry) {
            $calcType = strtolower($entry->calculation_type ?? '');
            $qty = 0;

            if (str_contains($calcType, 'kilometer')) {
                $calcKm = max(0, $entry->end_kilometer_reading - $entry->start_kilometer_reading);
                $qty = $entry->fuel_factor_km > 0 ? $calcKm / $entry->fuel_factor_km : 0;
            } elseif (str_contains($calcType, 'timeframe')) {
                if ($entry->end_time && $entry->start_time) {
                    $start = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->start_time->format('H:i:s'));
                    $end = Carbon::parse($entry->date->format('Y-m-d').' '.$entry->end_time->format('H:i:s'));
                    $calcHours = max(0, $start->diffInMinutes($end) / 60);
                    $qty = $calcHours * $entry->fuel_factor_hr;
                }
            } elseif (str_contains($calcType, 'actual')) {
                $qty = ($entry->actual_hours ?? 0) * $entry->fuel_factor_hr;
            } elseif (str_contains($calcType, 'hour')) {
                $calcHours = max(0, $entry->end_hour_reading - $entry->start_hour_reading);
                $qty = $calcHours * $entry->fuel_factor_hr;
            }
            $assetConsumed += $qty;
        }

        // 2. Direct fuel orders consumption (actual_quantity of DONE direct orders)
        $directConsumed = FuelOrder::whereNull('asset_id')
            ->where('sub_account_id', $this->id)
            ->where('status', 'DONE')
            ->sum('actual_quantity');

        return $assetConsumed + $directConsumed;
    }

    public function remainingBudget(): float
    {
        $approved = $this->budgets()->where('status', 'Approved')->sum('budget_quantity');

        return max(0.0, (float) $approved - $this->consumedBudget());
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name.($this->type === 'Uncontrolled' ? ' 🔓' : '');
    }
}
