<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    /**
     * Show the Super Admin login form.
     */
    public function showLogin(): View
    {
        return view('super-admin.login');
    }

    /**
     * Handle a login request for Super Admin.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('super-admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log out the Super Admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }

    /**
     * Show the Super Admin dashboard.
     */
    public function dashboard(): View
    {
        $companies = Company::with('subscriptionTier')->withCount('users', 'assets')->get();
        $tiers = SubscriptionTier::withCount('companies')->get();

        return view('super-admin.dashboard', compact('companies', 'tiers'));
    }

    /**
     * Store a newly created company, including its first company admin.
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subscription_tier_id' => 'required|exists:subscription_tiers,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        // Create the company
        $company = Company::create([
            'name' => $validated['company_name'],
            'subscription_tier_id' => $validated['subscription_tier_id'],
            'status' => 'active',
        ]);

        // Create the initial company administrator user
        User::create([
            'company_id' => $company->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'administrator',
            'is_temporary_password' => false,
        ]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Company and Administrator account created successfully.');
    }

    /**
     * Update the status or subscription of an existing company.
     */
    public function updateCompany(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_tier_id' => 'required|exists:subscription_tiers,id',
            'status' => 'required|in:active,suspended,cancelled',
        ]);

        $company->update($validated);

        return redirect()->route('super-admin.dashboard')->with('status', 'Company updated successfully.');
    }

    /**
     * Store a new subscription tier.
     */
    public function storeTier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_tiers,name',
            'price' => 'nullable|required_without:is_free|numeric|min:0',
            'description' => 'nullable|string',
            'badge_text' => 'nullable|string|max:255',
            'max_users' => 'nullable|required_without:unlimited_max_users|integer|min:1',
            'max_assets' => 'nullable|required_without:unlimited_max_assets|integer|min:1',
            'max_classifications' => 'nullable|required_without:unlimited_max_classifications|integer|min:1',
            'max_accounts' => 'nullable|required_without:unlimited_max_accounts|integer|min:1',
            'max_sub_accounts_per_account' => 'nullable|required_without:unlimited_max_sub_accounts_per_account|integer|min:1',
            'reports_enabled' => 'nullable|boolean',
        ]);

        SubscriptionTier::create([
            'name' => $validated['name'],
            'price' => $request->has('is_free') ? 0.00 : $validated['price'],
            'description' => $validated['description'],
            'badge_text' => $validated['badge_text'],
            'max_users' => $request->has('unlimited_max_users') ? null : $validated['max_users'],
            'max_assets' => $request->has('unlimited_max_assets') ? null : $validated['max_assets'],
            'max_classifications' => $request->has('unlimited_max_classifications') ? null : $validated['max_classifications'],
            'max_accounts' => $request->has('unlimited_max_accounts') ? null : $validated['max_accounts'],
            'max_sub_accounts_per_account' => $request->has('unlimited_max_sub_accounts_per_account') ? null : $validated['max_sub_accounts_per_account'],
            'features' => [
                'reports' => ! empty($validated['reports_enabled']),
            ],
        ]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Subscription tier created successfully.');
    }

    /**
     * Update an existing subscription tier's limits.
     */
    public function updateTier(Request $request, SubscriptionTier $tier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_tiers,name,' . $tier->id,
            'price' => 'nullable|required_without:is_free|numeric|min:0',
            'description' => 'nullable|string',
            'badge_text' => 'nullable|string|max:255',
            'max_users' => 'nullable|required_without:unlimited_max_users|integer|min:1',
            'max_assets' => 'nullable|required_without:unlimited_max_assets|integer|min:1',
            'max_classifications' => 'nullable|required_without:unlimited_max_classifications|integer|min:1',
            'max_accounts' => 'nullable|required_without:unlimited_max_accounts|integer|min:1',
            'max_sub_accounts_per_account' => 'nullable|required_without:unlimited_max_sub_accounts_per_account|integer|min:1',
            'reports_enabled' => 'nullable|boolean',
        ]);

        $tier->update([
            'name' => $validated['name'],
            'price' => $request->has('is_free') ? 0.00 : $validated['price'],
            'description' => $validated['description'],
            'badge_text' => $validated['badge_text'],
            'max_users' => $request->has('unlimited_max_users') ? null : $validated['max_users'],
            'max_assets' => $request->has('unlimited_max_assets') ? null : $validated['max_assets'],
            'max_classifications' => $request->has('unlimited_max_classifications') ? null : $validated['max_classifications'],
            'max_accounts' => $request->has('unlimited_max_accounts') ? null : $validated['max_accounts'],
            'max_sub_accounts_per_account' => $request->has('unlimited_max_sub_accounts_per_account') ? null : $validated['max_sub_accounts_per_account'],
            'features' => [
                'reports' => ! empty($validated['reports_enabled']),
            ],
        ]);

        return redirect()->route('super-admin.dashboard')->with('status', 'Subscription tier limits updated successfully.');
    }
}
