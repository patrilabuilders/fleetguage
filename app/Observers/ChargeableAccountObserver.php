<?php

namespace App\Observers;

use App\Models\ChargeableAccount;
use App\Models\Company;
use Illuminate\Validation\ValidationException;

class ChargeableAccountObserver
{
    /**
     * Handle the ChargeableAccount "creating" event.
     */
    public function creating(ChargeableAccount $account): void
    {
        $companyId = $account->company_id ?: (auth()->check() ? auth()->user()->company_id : null);

        if ($companyId) {
            $company = Company::with('subscriptionTier')->find($companyId);
            if ($company && $company->subscriptionTier) {
                $maxAccounts = $company->subscriptionTier->max_accounts;
                if ($maxAccounts !== null) {
                    $currentCount = ChargeableAccount::withoutGlobalScopes()->where('company_id', $companyId)->count();
                    if ($currentCount >= $maxAccounts) {
                        throw ValidationException::withMessages([
                            'name' => ["Your subscription tier limits the number of accounts to {$maxAccounts}. Please upgrade to Enterprise."],
                        ]);
                    }
                }
            }
        }
    }
}
