<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $tiers = \App\Models\SubscriptionTier::all();
        if ($tiers->isEmpty()) {
            // Seed defaults dynamically to avoid blank form
            \App\Models\SubscriptionTier::create([
                'name' => 'Standard',
                'max_users' => 5,
                'max_assets' => 10,
                'features' => ['reports' => false],
            ]);
            \App\Models\SubscriptionTier::create([
                'name' => 'Enterprise',
                'max_users' => 100,
                'max_assets' => 1000,
                'features' => ['reports' => true],
            ]);
            $tiers = \App\Models\SubscriptionTier::all();
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
        $company = \App\Models\Company::create([
            'name' => $request->company_name,
            'subscription_tier_id' => $request->subscription_tier_id,
            'status' => 'active',
        ]);

        // Seed default AssetTypes for this company to give them an awesome onboarding experience
        $defaultTypes = ['Sedan', 'SUV', 'Truck', 'Van', 'Excavator'];
        foreach ($defaultTypes as $typeName) {
            \App\Models\AssetType::create([
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
