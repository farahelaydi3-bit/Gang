<x-guest-layout>

    <h2 class="text-2xl font-bold text-center mb-2">
        {{ __('Register as Court Owner') }}
    </h2>

    <p class="text-sm text-gray-500 text-center mb-6">
        {{ __('Create your CourtBook owner account') }}
    </p>

    <form method="POST" action="{{ route('admin.register.store') }}">
        @csrf

        <!-- Full Name -->
        <div>
            <x-input-label for="name" value="Full Name" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="block mt-1 w-full"
                :value="old('name')"
                required
                autofocus />

            <x-input-error
                :messages="$errors->get('name')"
                class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" value="Email Address" />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="block mt-1 w-full"
                :value="old('email')"
                required />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" value="Phone Number" />

            <x-text-input
                id="phone"
                name="phone"
                type="tel"
                class="block mt-1 w-full"
                :value="old('phone')"
                required />

            <x-input-error
                :messages="$errors->get('phone')"
                class="mt-2" />
        </div>

        <!-- Business Name -->
        <div class="mt-4">
            <x-input-label
                for="business_name"
                value="Business Name" />

            <x-text-input
                id="business_name"
                name="business_name"
                type="text"
                class="block mt-1 w-full"
                :value="old('business_name')"
                required />

            <x-input-error
                :messages="$errors->get('business_name')"
                class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                name="password"
                type="password"
                class="block mt-1 w-full"
                required />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label
                for="password_confirmation"
                value="Confirm Password" />

            <x-text-input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="block mt-1 w-full"
                required />
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between mt-6">

            <a href="{{ route('admin.login') }}"
                class="text-sm text-indigo-600 hover:underline">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button>
                {{ __('Create Owner Account') }}
            </x-primary-button>

        </div>

        <p class="text-xs text-gray-500 text-center mt-5">
            {{ __('Your account will be reviewed before you can manage courts.') }}
        </p>

    </form>

</x-guest-layout>