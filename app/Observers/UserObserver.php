<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        $companyId = $user->company_id ?: (auth()->check() ? auth()->user()->company_id : null);

        if ($companyId) {
            $company = Company::with('subscriptionTier')->find($companyId);
            if ($company && $company->subscriptionTier) {
                $maxUsers = $company->subscriptionTier->max_users;
                if ($maxUsers !== null) {
                    $currentUsersCount = User::withoutGlobalScopes()->where('company_id', $companyId)->count();
                    if ($currentUsersCount >= $maxUsers) {
                        throw ValidationException::withMessages([
                            'email' => ["Your subscription tier limits the number of users to {$maxUsers}. Please upgrade to Enterprise."],
                        ]);
                    }
                }
            }
        }
    }
}
