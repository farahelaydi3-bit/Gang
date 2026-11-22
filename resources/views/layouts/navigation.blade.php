<nav class="bg-white border-b border-gray-100" aria-label="{{ __('Main navigation') }}">
    <div class="flex justify-end px-4 pt-2"><x-language-toggle /></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between min-h-16 gap-6">
            <a href="{{ route('courtbook.home') }}" aria-label="{{ __('Home') }}">
                <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
            </a>

            <div class="flex items-center gap-4">
                <a href="{{ route('courtbook.home') }}">{{ __('Home') }}</a>
                <a href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>

                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">{{ __('Log out') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>
