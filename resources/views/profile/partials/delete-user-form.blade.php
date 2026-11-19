<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Deleting your account permanently removes your profile and data.') }}
        </p>
    </header>

    <details class="space-y-4">
        <summary class="cursor-pointer text-red-700">
            {{ __('Delete Account') }}
        </summary>

        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')

            <p class="text-sm text-gray-600">
                {{ __('Enter your password to confirm account deletion.') }}
            </p>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <x-danger-button type="submit">{{ __('Delete Account') }}</x-danger-button>
        </form>
    </details>
</section>
