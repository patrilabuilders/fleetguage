<?php

namespace App\Observers;

use App\Models\AssetType;
use App\Models\Company;
use Illuminate\Validation\ValidationException;

class AssetTypeObserver
{
    /**
     * Handle the AssetType "creating" event.
     */
    public function creating(AssetType $assetType): void
    {
        $companyId = $assetType->company_id ?: (auth()->check() ? auth()->user()->company_id : null);

        if ($companyId) {
            $company = Company::with('subscriptionTier')->find($companyId);
            if ($company && $company->subscriptionTier) {
                $maxClassifications = $company->subscriptionTier->max_classifications ?? 5;
                $currentCount = AssetType::withoutGlobalScopes()->where('company_id', $companyId)->count();
                if ($currentCount >= $maxClassifications) {
                    throw ValidationException::withMessages([
                        'name' => ["Your subscription tier limits the number of classifications to {$maxClassifications}. Please upgrade to Enterprise."],
                    ]);
                }
            }
        }
    }
}
