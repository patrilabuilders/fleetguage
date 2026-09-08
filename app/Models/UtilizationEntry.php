<?php

namespace App\Models;

use App\Traits\Tenantable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UtilizationEntry extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id',
        'asset_id',
        'date',
        'start_time',
        'end_time',
        'actual_hours',
        'reference',
        'particulars',
        'start_kilometer_reading',
        'end_kilometer_reading',
        'fuel_factor_km',
        'start_hour_reading',
        'end_hour_reading',
        'fuel_factor_hr',
        'driver_operator_name',
        'chargeable_account_id',
        'sub_account_id',
        'fuel_order_id',
        'calculation_type',
        'unbudgeted',
        'remarks',
        'last_kilometer_reading',
        'last_engine_hours',
        'last_date',
        'last_time',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'actual_hours' => 'float',
        'start_kilometer_reading' => 'float',
        'end_kilometer_reading' => 'float',
        'start_hour_reading' => 'float',
        'end_hour_reading' => 'float',
        'unbudgeted' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function fuelOrder(): BelongsTo
    {
        return $this->belongsTo(FuelOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }

    public function getCalculatedQuantityAttribute(): float
    {
        $calcType = strtolower($this->calculation_type ?? '');

        if (str_contains($calcType, 'kilometer')) {
            $calcKm = max(0, $this->end_kilometer_reading - $this->start_kilometer_reading);

            return $this->fuel_factor_km > 0 ? $calcKm / $this->fuel_factor_km : 0;
        } elseif (str_contains($calcType, 'timeframe')) {
            if ($this->end_time && $this->start_time) {
                $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : Carbon::parse($this->date)->format('Y-m-d');
                $startStr = $this->start_time instanceof Carbon ? $this->start_time->format('H:i:s') : Carbon::parse($this->start_time)->format('H:i:s');
                $endStr = $this->end_time instanceof Carbon ? $this->end_time->format('H:i:s') : Carbon::parse($this->end_time)->format('H:i:s');

                $start = Carbon::parse($dateStr.' '.$startStr);
                $end = Carbon::parse($dateStr.' '.$endStr);
                $calcHours = max(0, $start->diffInMinutes($end) / 60);

                return $calcHours * $this->fuel_factor_hr;
            }
        } elseif (str_contains($calcType, 'actual')) {
            return ($this->actual_hours ?? 0) * $this->fuel_factor_hr;
        } elseif (str_contains($calcType, 'hour')) {
            $calcHours = max(0, $this->end_hour_reading - $this->start_hour_reading);

            return $calcHours * $this->fuel_factor_hr;
        }

        return 0;
    }
}
