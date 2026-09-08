<?php

namespace App\Observers;

use App\Models\Asset;
use Illuminate\Validation\ValidationException;

class AssetObserver
{
    /**
     * Handle the Asset "creating" event.
     */
    public function creating(Asset $asset): void
    {
        $companyId = $asset->company_id ?: (auth()->check() ? auth()->user()->company_id : null);

        if ($companyId) {
            $company = \App\Models\Company::with('subscriptionTier')->find($companyId);
            if ($company && $company->subscriptionTier) {
                $maxAssets = $company->subscriptionTier->max_assets;
                $currentAssetsCount = Asset::withoutGlobalScopes()->where('company_id', $companyId)->count();
                if ($currentAssetsCount >= $maxAssets) {
                    throw ValidationException::withMessages([
                        'fleet_no' => ["Your subscription tier limits the number of assets to {$maxAssets}. Please upgrade to Enterprise."],
                    ]);
                }
            }
        }
    }
}
