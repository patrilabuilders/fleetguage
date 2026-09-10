<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AssetType;
use App\Models\Company;
use App\Models\SubscriptionTier;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $tiers = SubscriptionTier::all();
        if ($tiers->isEmpty()) {
            // Seed defaults dynamically to avoid blank form
            SubscriptionTier::create([
                'name' => 'Standard',
                'price' => 49.00,
                'description' => 'Perfect for regional construction sites or single-depot logistics hubs.',
                'max_users' => 5,
                'max_assets' => 10,
                'max_classifications' => 5,
                'max_accounts' => 10,
                'max_sub_accounts_per_account' => 10,
                'features' => ['reports' => false],
            ]);
            SubscriptionTier::create([
                'name' => 'Enterprise',
                'price' => 299.00,
                'description' => 'Complete solution for industrial operations, mining networks, and national shipping fleets.',
                'max_users' => 100,
                'max_assets' => 1000,
                'max_classifications' => 100,
                'max_accounts' => 200,
                'max_sub_accounts_per_account' => 500,
                'features' => ['reports' => true],
            ]);
            $tiers = SubscriptionTier::all();
        }

        return view('auth.register', compact('tiers'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'subscription_tier_id' => ['required', 'exists:subscription_tiers,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the Company first
        $company = Company::create([
            'name' => $request->company_name,
            'subscription_tier_id' => $request->subscription_tier_id,
            'status' => 'active',
        ]);

        // Seed default AssetTypes for this company to give them an awesome onboarding experience
        $defaultTypes = ['Sedan', 'SUV', 'Truck', 'Van', 'Excavator'];
        foreach ($defaultTypes as $typeName) {
            AssetType::create([
                'company_id' => $company->id,
                'name' => $typeName,
            ]);
        }

        // Create the primary Company Administrator user
        $user = User::create([
            'company_id' => $company->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'administrator',
            'is_temporary_password' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
