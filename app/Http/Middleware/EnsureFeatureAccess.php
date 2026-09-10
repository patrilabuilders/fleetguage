<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if ($user && $user->company_id) {
            $company = Company::with('subscriptionTier')->find($user->company_id);
            if ($company && $company->subscriptionTier) {
                $features = $company->subscriptionTier->features ?? [];
                if (! empty($features[$feature])) {
                    return $next($request);
                }
            }
        }

        abort(403, "Your subscription tier does not include access to the '{$feature}' feature. Please upgrade to Enterprise.");
    }
}
