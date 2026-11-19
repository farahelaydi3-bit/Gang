<x-guest-layout>
    <form method="POST" action="{{ route('super_admin.login.submit') }}">
        @csrf

        <h2 class="text-2xl font-bold mb-5">Super Admin Login</h2>

        <div>
            <x-input-label for="email" value="Email" />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="block mt-1 w-full"
                :value="old('email')"
                required autofocus
            />

            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                name="password"
                type="password"
                class="block mt-1 w-full"
                required
            />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mt-4">
            <x-primary-button>Login</x-primary-button>
        </div>
    </form>
</x-guest-layout>