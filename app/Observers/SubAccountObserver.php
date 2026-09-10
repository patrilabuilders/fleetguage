<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\SubAccount;
use Illuminate\Validation\ValidationException;

class SubAccountObserver
{
    /**
     * Handle the SubAccount "creating" event.
     */
    public function creating(SubAccount $subAccount): void
    {
        $companyId = $subAccount->company_id ?: (auth()->check() ? auth()->user()->company_id : null);

        if ($companyId) {
            $company = Company::with('subscriptionTier')->find($companyId);
            if ($company && $company->subscriptionTier) {
                $maxSubAccounts = $company->subscriptionTier->max_sub_accounts_per_account ?? 10;
                $currentCount = SubAccount::withoutGlobalScopes()
                    ->where('chargeable_account_id', $subAccount->chargeable_account_id)
                    ->count();
                if ($currentCount >= $maxSubAccounts) {
                    throw ValidationException::withMessages([
                        'name' => ["Your subscription tier limits the number of sub-accounts per account to {$maxSubAccounts}. Please upgrade to Enterprise."],
                    ]);
                }
            }
        }
    }
}
