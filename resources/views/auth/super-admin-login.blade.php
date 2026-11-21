<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Sign In | Gang</title>
    <link rel="icon" href="{{ asset('courtbook-assets/icon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/super-admin-login.css') }}">
</head>
<body class="super-login">
    <main class="super-login-shell">
        <header class="super-login-top">
            <a class="super-login-home" href="{{ route('courtbook.home') }}">
                <span aria-hidden="true">←</span> Back to Gang
            </a>
        </header>

        <div class="super-login-card">
            <section class="overview-welcome super-login-brand" aria-labelledby="super-login-brand-title">
                <div class="super-login-brand-content">
                    <img class="super-login-logo" src="{{ asset('courtbook-assets/images/gang-logo.png') }}" alt="" width="753" height="489">
                    <h2 id="super-login-brand-title">Gang Control Center</h2>
                    <p>Manage courts, players, and owner applications.</p>
                </div>

                <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
                <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
                <div class="super-login-court" aria-hidden="true">
                    <span></span>
                    <span></span>
                </div>
            </section>

            <section class="super-login-panel" aria-labelledby="super-login-title">
                <h1 id="super-login-title">Super Admin Sign In</h1>

                <form method="POST" action="{{ route('super_admin.login.submit') }}">
                    @csrf

                    <div class="super-login-field">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username"
                            @error('email') aria-invalid="true" aria-describedby="super-login-email-error" @enderror required>
                        @error('email')
                            <p class="super-login-error" id="super-login-email-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="super-login-field">
                        <label for="password">Password</label>
                        <div class="super-login-password">
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                @error('password') aria-invalid="true" aria-describedby="super-login-password-error" @enderror required>
                            <button type="button" id="super-login-password-toggle" aria-controls="password" hidden>Show</button>
                        </div>
                        @error('password')
                            <p class="super-login-error" id="super-login-password-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="super-login-submit" type="submit">
                        Sign in <span aria-hidden="true">→</span>
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script>
        const password = document.getElementById('password');
        const passwordToggle = document.getElementById('super-login-password-toggle');

        passwordToggle.hidden = false;
        passwordToggle.addEventListener('click', () => {
            const visible = password.type === 'password';
            password.type = visible ? 'text' : 'password';
            passwordToggle.textContent = visible ? 'Hide' : 'Show';
        });
    </script>
</body>
</html>
