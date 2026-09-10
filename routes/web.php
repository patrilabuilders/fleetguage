<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\ChargeableAccountController;
use App\Http\Controllers\FuelOrderController;
use App\Http\Controllers\JpmController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\PublicDashboardLinkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubAccountBudgetController;
use App\Http\Controllers\SubAccountController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilizationEntryController;
use Illuminate\Support\Facades\Route;

// Super Admin Routes
Route::prefix('super-admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [SuperAdminController::class, 'showLogin'])->name('super-admin.login');
        Route::post('login', [SuperAdminController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [SuperAdminController::class, 'logout'])->name('super-admin.logout');
        Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('super-admin.dashboard');
        Route::post('companies', [SuperAdminController::class, 'storeCompany'])->name('super-admin.companies.store');
        Route::patch('companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('super-admin.companies.update');
        Route::post('tiers', [SuperAdminController::class, 'storeTier'])->name('super-admin.tiers.store');
        Route::patch('tiers/{tier}', [SuperAdminController::class, 'updateTier'])->name('super-admin.tiers.update');
    });
});

Route::get('/', function () {
    $tiers = \App\Models\SubscriptionTier::all();
    if ($tiers->isEmpty()) {
        \App\Models\SubscriptionTier::create([
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
        \App\Models\SubscriptionTier::create([
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
        $tiers = \App\Models\SubscriptionTier::all();
    }
    return view('welcome', compact('tiers'));
});

Route::get('/dashboard', [ReportController::class, 'accountBudgetDashboard'])
    ->middleware(['auth', 'verified', 'check_temp_password'])->name('dashboard');

Route::middleware(['auth', 'check_temp_password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports
    Route::middleware('feature:reports')->group(function () {
        Route::get('reports/asset-utilization', [ReportController::class, 'assetUtilization'])->name('reports.asset-utilization');
        Route::get('reports/fuel-orders', [ReportController::class, 'fuelOrdersSummary'])->name('reports.fuel-orders');
        Route::get('reports/chargeable-accounts', [ReportController::class, 'chargeableAccountSummary'])->name('reports.chargeable-accounts');
    });
    Route::get('dashboard/account/{chargeable_account}/sub-accounts', [ReportController::class, 'subAccountDashboard'])->name('dashboard.sub-accounts');

    // Shared Dashboard Management
    Route::get('shared-links', [PublicDashboardLinkController::class, 'index'])->name('public-dashboard-links.index');
    Route::post('shared-links', [PublicDashboardLinkController::class, 'store'])->name('public-dashboard-links.store');
    Route::patch('shared-links/{link}/toggle', [PublicDashboardLinkController::class, 'toggleStatus'])->name('public-dashboard-links.toggle');
    Route::delete('shared-links/{link}', [PublicDashboardLinkController::class, 'destroy'])->name('public-dashboard-links.destroy');

    // Assets
    Route::resource('assets', AssetController::class);
    Route::get('assets/{asset}/logs', [UtilizationEntryController::class, 'logs'])->name('assets.logs');
    Route::get('assets/{asset}/logs/print', [UtilizationEntryController::class, 'printLogs'])->name('assets.logs.print');
    Route::get('assets/{asset}/utilization-entries/bulk-upload', [UtilizationEntryController::class, 'bulkUpload'])->name('assets.utilization-entries.bulk-upload');
    Route::post('assets/{asset}/utilization-entries/bulk-preview', [UtilizationEntryController::class, 'bulkPreview'])->name('assets.utilization-entries.bulk-preview');
    Route::post('assets/{asset}/utilization-entries/bulk-store', [UtilizationEntryController::class, 'bulkStore'])->name('assets.utilization-entries.bulk-store');
    Route::get('assets/{asset}/utilization-entries/bulk-template', [UtilizationEntryController::class, 'bulkTemplate'])->name('assets.utilization-entries.bulk-template');
    Route::post('assets/{asset}/utilization-entries/bulk-upload-chunk', [UtilizationEntryController::class, 'bulkUploadChunk'])->name('assets.utilization-entries.bulk-upload-chunk');

    // Sub Accounts JSON (needed by utilization form for all roles)
    Route::get('chargeable-accounts/{chargeable_account}/sub-accounts/json', [SubAccountController::class, 'byAccount'])->name('chargeable-accounts.sub-accounts.json');

    // Utilization Entries
    Route::get('utilization-entries/print', [UtilizationEntryController::class, 'print'])->name('utilization-entries.print');
    Route::resource('utilization-entries', UtilizationEntryController::class)->except(['create'])->withTrashed();

    // Fuel Orders
    Route::get('fuel-orders/print', [FuelOrderController::class, 'print'])->name('fuel-orders.print');
    Route::resource('fuel-orders', FuelOrderController::class)->except(['destroy']);
    Route::get('fuel-orders/{fuel_order}/actualize', [FuelOrderController::class, 'actualize'])->name('fuel-orders.actualize');
    Route::post('fuel-orders/{fuel_order}/actualize', [FuelOrderController::class, 'storeActualization'])->name('fuel-orders.store-actualization');

    // Admin, Moderator and Budgeteer routes for accounts
    Route::middleware('role:administrator,moderator,budgeteer')->group(function () {
        Route::resource('chargeable-accounts', ChargeableAccountController::class);
        Route::get('chargeable-accounts/{chargeable_account}/print', [ChargeableAccountController::class, 'print'])->name('chargeable-accounts.print');
        Route::get('sub-accounts/{sub_account}', [SubAccountController::class, 'show'])->name('sub-accounts.show');
        Route::get('sub-accounts/{sub_account}/edit', [SubAccountController::class, 'edit'])->name('sub-accounts.edit');
        Route::patch('sub-accounts/{sub_account}', [SubAccountController::class, 'update'])->name('sub-accounts.update');
        Route::post('chargeable-accounts/{chargeable_account}/sub-accounts', [SubAccountController::class, 'store'])->name('chargeable-accounts.sub-accounts.store');
        Route::delete('sub-accounts/{sub_account}', [SubAccountController::class, 'destroy'])->name('sub-accounts.destroy');
        Route::post('sub-accounts/{sub_account}/accomplishments', [SubAccountController::class, 'storeAccomplishment'])->name('sub-accounts.accomplishments.store');
        Route::delete('accomplishments/{accomplishment}', [SubAccountController::class, 'destroyAccomplishment'])->name('sub-accounts.accomplishments.destroy');
    });

    // Admin and Moderator only routes for budget approval
    Route::middleware('role:administrator,moderator')->group(function () {
        Route::patch('account-budgets/{account_budget}/approve', [SubAccountBudgetController::class, 'approve'])->name('account-budgets.approve');
        Route::patch('account-budgets/{account_budget}/reject', [SubAccountBudgetController::class, 'reject'])->name('account-budgets.reject');
        Route::post('fuel-orders/{fuel_order}/unlink-sub-account', [FuelOrderController::class, 'unlinkSubAccount'])->name('fuel-orders.unlink-sub-account');
    });

    // Admin, Moderator and Budgeteer routes
    Route::middleware('role:administrator,moderator,budgeteer')->group(function () {
        Route::get('account-budgets/print', [SubAccountBudgetController::class, 'print'])->name('account-budgets.print');
        Route::resource('account-budgets', SubAccountBudgetController::class)->except(['create']);
    });

    // Admin only routes
    Route::middleware('role:administrator')->group(function () {
        // Settings Hub
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');

        // Asset Classifications
        Route::resource('asset-types', AssetTypeController::class);

        // User management creation routes (MUST be before resource route to avoid wildcard conflict)
        Route::get('users/create-moderator', [UserController::class, 'createModerator'])->name('users.create-moderator');
        Route::get('users/create-data-logger', [UserController::class, 'createDataLogger'])->name('users.create-data-logger');
        Route::get('users/create-fuel-man', [UserController::class, 'createFuelMan'])->name('users.create-fuel-man');
        Route::get('users/create-budgeteer', [UserController::class, 'createBudgeteer'])->name('users.create-budgeteer');

        // User management resource
        Route::resource('users', UserController::class)->except(['create', 'show']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Void Fuel Order
        Route::post('fuel-orders/{fuel_order}/void', [FuelOrderController::class, 'void'])->name('fuel-orders.void');

        // Approve Fuel Order Waiver
        Route::post('fuel-orders/{fuel_order}/approve-waiver', [FuelOrderController::class, 'approveWaiver'])->name('fuel-orders.approve-waiver');

        // Merge Sub Account
        Route::post('sub-accounts/{sub_account}/merge', [SubAccountController::class, 'merge'])->name('sub-accounts.merge');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.edit_password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update_password');
});

require __DIR__.'/auth.php';

// Public Shared Dashboard
Route::get('/shared/dashboard/{slug}', [PublicDashboardController::class, 'show'])->name('public.dashboard');
Route::get('/shared/dashboard/{slug}/manifest.json', [PublicDashboardController::class, 'manifest'])->name('public.dashboard.manifest');
Route::get('/shared/dashboard/{slug}/account/{chargeable_account}/sub-accounts', [PublicDashboardController::class, 'subAccountDashboard'])->name('public.dashboard.sub-accounts');

// JPM 8000 Standalone Application Route
Route::get('/jpm8000/{path?}', [JpmController::class, 'serve'])->where('path', '.*')->name('jpm8000');
