<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Super Admin Dashboard - {{ config('app.name', 'FleetGuage') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-dark text-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary sticky-top py-3">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-3 me-4" href="{{ route('super-admin.dashboard') }}">
                <div class="bg-primary bg-opacity-25 p-2 rounded-3">
                    <x-application-logo class="text-primary" style="height: 24px; width: auto;" />
                </div>
                <span class="fw-black text-uppercase tracking-tight" style="font-size: 1.25rem;">Super Admin Panel</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-secondary d-none d-md-inline-block">Logged in as: <strong class="text-light">{{ Auth::guard('admin')->user()->name }}</strong></span>
                <form method="POST" action="{{ route('super-admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-xl py-5">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-25 text-success" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Header Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 rounded-4 p-4">
                    <small class="text-secondary text-uppercase fw-bold tracking-wider">Total Companies</small>
                    <h2 class="display-5 fw-black mb-0 text-primary mt-2">{{ $companies->count() }}</h2>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 rounded-4 p-4">
                    <small class="text-secondary text-uppercase fw-bold tracking-wider">Subscription Tiers</small>
                    <h2 class="display-5 fw-black mb-0 text-success mt-2">{{ $tiers->count() }}</h2>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side: Manage Companies -->
            <div class="col-lg-8">
                <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 rounded-4 p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h4 class="mb-0 fw-black text-uppercase tracking-tight">Companies / Tenants</h4>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                            Add Company
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-secondary" style="font-size: 0.85rem;">
                                    <th>NAME</th>
                                    <th>SUBSCRIPTION TIER</th>
                                    <th class="text-center">USERS</th>
                                    <th class="text-center">ASSETS</th>
                                    <th>STATUS</th>
                                    <th class="text-end">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companies as $company)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $company->name }}</div>
                                            <small class="text-secondary">ID: {{ $company->id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                {{ $company->subscriptionTier->name }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold text-secondary">{{ $company->users_count }}</td>
                                        <td class="text-center fw-semibold text-secondary">{{ $company->assets_count }}</td>
                                        <td>
                                            @if($company->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($company->status === 'suspended')
                                                <span class="badge bg-warning text-dark">Suspended</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editCompanyModal{{ $company->id }}">
                                                Edit Status / Subscription
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Company Modal -->
                                    <div class="modal fade" id="editCompanyModal{{ $company->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('super-admin.companies.update', $company->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-content bg-dark border-secondary">
                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title fw-bold">Edit Company: {{ $company->name }}</h5>
                                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Subscription Tier</label>
                                                            <select class="form-select bg-secondary bg-opacity-25 text-light border-secondary" name="subscription_tier_id" required>
                                                                @foreach($tiers as $tier)
                                                                    <option value="{{ $tier->id }}" {{ $company->subscription_tier_id === $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select bg-secondary bg-opacity-25 text-light border-secondary" name="status" required>
                                                                <option value="active" {{ $company->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="suspended" {{ $company->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                                <option value="cancelled" {{ $company->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-4">No companies registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Manage Subscription Tiers -->
            <div class="col-lg-4">
                <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 rounded-4 p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h4 class="mb-0 fw-black text-uppercase tracking-tight">Subscription Tiers</h4>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createTierModal">
                            Add Tier
                        </button>
                    </div>

                    <div class="row g-3">
                        @forelse($tiers as $tier)
                            <div class="col-12">
                                <div class="card bg-secondary bg-opacity-25 border-secondary border-opacity-50 rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0 fw-bold text-success">{{ $tier->name }}</h5>
                                        <button class="btn btn-link btn-sm text-secondary p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#editTierModal{{ $tier->id }}">
                                            Edit Limits
                                        </button>
                                    </div>
                                    <div class="row text-secondary g-2" style="font-size: 0.9rem;">
                                        <div class="col-12 mb-1">
                                            Price: <strong class="text-light">{{ !is_null($tier->price) ? ($tier->price == 0 ? 'Free' : '$' . number_format($tier->price, 2)) : 'Not set' }}</strong>
                                        </div>
                                        @if($tier->description)
                                            <div class="col-12 mb-2 small text-secondary">
                                                <em>{{ $tier->description }}</em>
                                            </div>
                                        @endif
                                        <div class="col-6">Max Users: <strong class="text-light">{{ $tier->max_users ?? 'Infinite' }}</strong></div>
                                        <div class="col-6">Max Assets: <strong class="text-light">{{ $tier->max_assets ?? 'Infinite' }}</strong></div>
                                        <div class="col-6">Max Classifications: <strong class="text-light">{{ $tier->max_classifications ?? 'Infinite' }}</strong></div>
                                        <div class="col-6">Max Accounts: <strong class="text-light">{{ $tier->max_accounts ?? 'Infinite' }}</strong></div>
                                        <div class="col-12">Max Sub Accounts per Account: <strong class="text-light">{{ $tier->max_sub_accounts_per_account ?? 'Infinite' }}</strong></div>
                                        <div class="col-12">
                                            Reports: 
                                            @if(!empty($tier->features['reports']))
                                                <span class="text-success fw-bold">✓ Enabled</span>
                                            @else
                                                <span class="text-danger fw-bold">✗ Disabled</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Tier Modal -->
                            <div class="modal fade" id="editTierModal{{ $tier->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('super-admin.tiers.update', $tier->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-content bg-dark border-secondary">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title fw-bold">Edit Tier: {{ $tier->name }}</h5>
                                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Price ($ / month)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="price" id="edit_price_{{ $tier->id }}" value="{{ $tier->price }}" @if(!is_null($tier->price) && $tier->price > 0) required min="0" @else disabled @endif placeholder="e.g. 49.00">
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="is_free" id="edit_is_free_{{ $tier->id }}" value="1" {{ ($tier->price === 0.0 || $tier->price === 0) ? 'checked' : '' }} onchange="togglePriceInput('edit_price_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_is_free_{{ $tier->id }}">Free</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="description" rows="2" placeholder="Describe this tier's key value proposition...">{{ $tier->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Max Users</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_users" id="edit_max_users_{{ $tier->id }}" value="{{ $tier->max_users }}" @if(!is_null($tier->max_users)) required min="1" @else disabled @endif>
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_users" id="edit_unlimited_max_users_{{ $tier->id }}" value="1" {{ is_null($tier->max_users) ? 'checked' : '' }} onchange="toggleLimitInput('edit_max_users_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_unlimited_max_users_{{ $tier->id }}">Infinite</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Max Assets</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_assets" id="edit_max_assets_{{ $tier->id }}" value="{{ $tier->max_assets }}" @if(!is_null($tier->max_assets)) required min="1" @else disabled @endif>
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_assets" id="edit_unlimited_max_assets_{{ $tier->id }}" value="1" {{ is_null($tier->max_assets) ? 'checked' : '' }} onchange="toggleLimitInput('edit_max_assets_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_unlimited_max_assets_{{ $tier->id }}">Infinite</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Max Classifications</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_classifications" id="edit_max_classifications_{{ $tier->id }}" value="{{ $tier->max_classifications }}" @if(!is_null($tier->max_classifications)) required min="1" @else disabled @endif>
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_classifications" id="edit_unlimited_max_classifications_{{ $tier->id }}" value="1" {{ is_null($tier->max_classifications) ? 'checked' : '' }} onchange="toggleLimitInput('edit_max_classifications_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_unlimited_max_classifications_{{ $tier->id }}">Infinite</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Max Accounts</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_accounts" id="edit_max_accounts_{{ $tier->id }}" value="{{ $tier->max_accounts }}" @if(!is_null($tier->max_accounts)) required min="1" @else disabled @endif>
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_accounts" id="edit_unlimited_max_accounts_{{ $tier->id }}" value="1" {{ is_null($tier->max_accounts) ? 'checked' : '' }} onchange="toggleLimitInput('edit_max_accounts_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_unlimited_max_accounts_{{ $tier->id }}">Infinite</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Max Sub Accounts per Account</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_sub_accounts_per_account" id="edit_max_sub_accounts_per_account_{{ $tier->id }}" value="{{ $tier->max_sub_accounts_per_account }}" @if(!is_null($tier->max_sub_accounts_per_account)) required min="1" @else disabled @endif>
                                                        <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                                            <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_sub_accounts_per_account" id="edit_unlimited_max_sub_accounts_per_account_{{ $tier->id }}" value="1" {{ is_null($tier->max_sub_accounts_per_account) ? 'checked' : '' }} onchange="toggleLimitInput('edit_max_sub_accounts_per_account_{{ $tier->id }}', this)">
                                                            <label class="form-check-label small mb-0" for="edit_unlimited_max_sub_accounts_per_account_{{ $tier->id }}">Infinite</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="editReportsEnabled{{ $tier->id }}" name="reports_enabled" value="1" {{ !empty($tier->features['reports']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="editReportsEnabled{{ $tier->id }}">Enable Reports Feature</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Tier Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-secondary py-3">No subscription tiers.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Company Modal -->
    <div class="modal fade" id="createCompanyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('super-admin.companies.store') }}">
                @csrf
                <div class="modal-content bg-dark border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold">Add New Company & Admin</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-primary text-uppercase fw-bold tracking-wider mb-3">Company Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="company_name" required placeholder="e.g. Acme Corp">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subscription Tier</label>
                            <select class="form-select bg-secondary bg-opacity-25 text-light border-secondary" name="subscription_tier_id" required>
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="border-secondary my-4">

                        <h6 class="text-success text-uppercase fw-bold tracking-wider mb-3">First Company Administrator</h6>
                        <div class="mb-3">
                            <label class="form-label">Admin Name</label>
                            <input type="text" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="admin_name" required placeholder="e.g. John Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Email Address</label>
                            <input type="email" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="admin_email" required placeholder="e.g. john@acme.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Password</label>
                            <input type="password" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="admin_password" required minlength="8" placeholder="At least 8 characters">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Company & Admin</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Tier Modal -->
    <div class="modal fade" id="createTierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('super-admin.tiers.store') }}">
                @csrf
                <div class="modal-content bg-dark border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold">Add Subscription Tier</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tier Name</label>
                            <input type="text" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="name" required placeholder="e.g. Professional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price ($ / month)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="price" id="create_price" required min="0" placeholder="e.g. 49.00">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="is_free" id="create_is_free" value="1" onchange="togglePriceInput('create_price', this)">
                                    <label class="form-check-label small mb-0" for="create_is_free">Free</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="description" rows="2" placeholder="Describe this tier's key value proposition..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Users</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_users" id="create_max_users" required min="1" placeholder="e.g. 10">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_users" id="create_unlimited_max_users" value="1" onchange="toggleLimitInput('create_max_users', this)">
                                    <label class="form-check-label small mb-0" for="create_unlimited_max_users">Infinite</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Assets</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_assets" id="create_max_assets" required min="1" placeholder="e.g. 20">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_assets" id="create_unlimited_max_assets" value="1" onchange="toggleLimitInput('create_max_assets', this)">
                                    <label class="form-check-label small mb-0" for="create_unlimited_max_assets">Infinite</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Classifications</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_classifications" id="create_max_classifications" required min="1" placeholder="e.g. 5">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_classifications" id="create_unlimited_max_classifications" value="1" onchange="toggleLimitInput('create_max_classifications', this)">
                                    <label class="form-check-label small mb-0" for="create_unlimited_max_classifications">Infinite</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Accounts</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_accounts" id="create_max_accounts" required min="1" placeholder="e.g. 10">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_accounts" id="create_unlimited_max_accounts" value="1" onchange="toggleLimitInput('create_max_accounts', this)">
                                    <label class="form-check-label small mb-0" for="create_unlimited_max_accounts">Infinite</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Sub Accounts per Account</label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-secondary bg-opacity-25 text-light border-secondary" name="max_sub_accounts_per_account" id="create_max_sub_accounts_per_account" required min="1" placeholder="e.g. 10">
                                <div class="input-group-text bg-secondary bg-opacity-25 border-secondary">
                                    <input class="form-check-input mt-0 me-1" type="checkbox" name="unlimited_max_sub_accounts_per_account" id="create_unlimited_max_sub_accounts_per_account" value="1" onchange="toggleLimitInput('create_max_sub_accounts_per_account', this)">
                                    <label class="form-check-label small mb-0" for="create_unlimited_max_sub_accounts_per_account">Infinite</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="reportsEnabled" name="reports_enabled" value="1">
                            <label class="form-check-label" for="reportsEnabled">Enable Reports Feature</label>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Create Tier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleLimitInput(inputId, checkbox) {
            const input = document.getElementById(inputId);
            if (checkbox.checked) {
                input.disabled = true;
                input.removeAttribute('required');
                input.value = '';
            } else {
                input.disabled = false;
                input.setAttribute('required', 'required');
                input.setAttribute('min', '1');
                input.focus();
            }
        }

        function togglePriceInput(inputId, checkbox) {
            const input = document.getElementById(inputId);
            if (checkbox.checked) {
                input.disabled = true;
                input.removeAttribute('required');
                input.value = '';
            } else {
                input.disabled = false;
                input.setAttribute('required', 'required');
                input.setAttribute('min', '0');
                input.focus();
            }
        }
    </script>

</body>
</html>
