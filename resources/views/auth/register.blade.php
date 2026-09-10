<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="fw-black text-light tracking-tight mb-1" style="font-size: 1.75rem;">Register Your Fleet</h2>
        <p class="text-secondary small">Set up your enterprise fuel monitoring console in seconds.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" id="registrationForm" class="vstack gap-3">
        @csrf

        <!-- Section 1: Company details -->
        <div class="border-bottom border-secondary border-opacity-25 pb-3">
            <h6 class="text-uppercase tracking-widest text-primary fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.1em;">1. Organization Details</h6>
            
            <div class="mb-3">
                <x-input-label for="company_name" :value="__('Company Name')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <x-text-input id="company_name" class="w-100" style="padding: 0.75rem 1rem;" type="text" name="company_name" :value="old('company_name')" required autofocus placeholder="e.g. Nexus Logistics" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <!-- Subscription Tier Interactive Selector -->
            <div class="mb-3">
                <x-input-label :value="__('Select Subscription Tier')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <input type="hidden" name="subscription_tier_id" id="subscription_tier_id" value="{{ old('subscription_tier_id', request('tier_id', request('subscription_tier_id', $tiers->first()?->id ?? ''))) }}">
                
                <div class="row g-2">
                    @foreach($tiers as $tier)
                        <div class="col-6">
                            <div class="card h-100 bg-dark border-secondary border-opacity-25 rounded-3 p-3 text-center tier-card" 
                                 role="button" 
                                 data-tier-id="{{ $tier->id }}"
                                 style="cursor: pointer; transition: all 0.2s ease-in-out;">
                                <div class="fw-bold text-light mb-1">{{ $tier->name }}</div>
                                <div class="text-secondary mb-2" style="font-size: 0.75rem;">
                                    Max {{ $tier->max_users ?? 'Infinite' }} Users<br>
                                    Max {{ $tier->max_assets ?? 'Infinite' }} Assets
                                </div>
                                <div class="badge bg-secondary bg-opacity-25 text-light select-badge" style="font-size: 0.65rem;">Select</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('subscription_tier_id')" class="mt-2" />
            </div>
        </div>

        <!-- Section 2: Admin details -->
        <div class="pb-2">
            <h6 class="text-uppercase tracking-widest text-primary fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.1em;">2. Administrator Credentials</h6>

            <div class="mb-3">
                <x-input-label for="name" :value="__('Administrator Name')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <x-text-input id="name" class="w-100" style="padding: 0.75rem 1rem;" type="text" name="name" :value="old('name')" required placeholder="John Doe" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-3">
                <x-input-label for="email" :value="__('Professional Email')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <x-text-input id="email" class="w-100" style="padding: 0.75rem 1rem;" type="email" name="email" :value="old('email')" required placeholder="john.doe@company.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-3">
                <x-input-label for="password" :value="__('Password')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <x-text-input id="password" class="w-100" style="padding: 0.75rem 1rem;" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-3">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-secondary small fw-bold text-uppercase tracking-widest ms-1 mb-2" />
                <x-text-input id="password_confirmation" class="w-100" style="padding: 0.75rem 1rem;" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="pt-2">
            <x-primary-button class="w-100 py-3">
                {{ __('Initialize System') }}
            </x-primary-button>
        </div>

        <div class="text-center mt-3">
            <a class="text-decoration-none small text-secondary link-light" href="{{ route('login') }}" style="font-size: 0.8rem;">
                {{ __('Already registered? Sign In') }}
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tierCards = document.querySelectorAll('.tier-card');
            const hiddenInput = document.getElementById('subscription_tier_id');

            function selectTier(tierId) {
                tierCards.forEach(card => {
                    const selectBadge = card.querySelector('.select-badge');
                    if (card.getAttribute('data-tier-id') === tierId) {
                        card.style.borderColor = '#0d6efd';
                        card.style.backgroundColor = 'rgba(13, 110, 253, 0.08)';
                        if (selectBadge) {
                            selectBadge.classList.remove('bg-secondary', 'bg-opacity-25');
                            selectBadge.classList.add('bg-primary');
                            selectBadge.textContent = 'Selected';
                        }
                    } else {
                        card.style.borderColor = 'rgba(255, 255, 255, 0.08)';
                        card.style.backgroundColor = 'transparent';
                        if (selectBadge) {
                            selectBadge.classList.remove('bg-primary');
                            selectBadge.classList.add('bg-secondary', 'bg-opacity-25');
                            selectBadge.textContent = 'Select';
                        }
                    }
                });
                hiddenInput.value = tierId;
            }

            tierCards.forEach(card => {
                card.addEventListener('click', function() {
                    const tierId = this.getAttribute('data-tier-id');
                    selectTier(tierId);
                });
            });

            // Run initial selection
            if (hiddenInput.value) {
                selectTier(hiddenInput.value);
            } else if (tierCards.length > 0) {
                selectTier(tierCards[0].getAttribute('data-tier-id'));
            }
        });
    </script>
</x-guest-layout>
