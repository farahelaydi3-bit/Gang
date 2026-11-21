<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Phone - Gang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f8f5;
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
        }
        .card {
            background: white;
            padding: 36px;
            border-radius: 18px;
            width: min(400px, 90%);
            box-shadow: 0 12px 35px #00000012;
            text-align: center;
        }
        h1 { color: #166534; }
        input {
            width: 100%;
            box-sizing: border-box;
            padding: 14px;
            margin: 18px 0;
            font-size: 22px;
            text-align: center;
            letter-spacing: 6px;
            border: 1px solid #ccc;
            border-radius: 9px;
        }
        button {
            padding: 13px 20px;
            background: #166534;
            color: white;
            border: 0;
            border-radius: 9px;
            cursor: pointer;
        }
        .error { color: #b91c1c; }
        .success { color: #166534; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Verify your phone</h1>
        <p>Enter the 6-digit code sent to your phone.</p>

        @if(session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        @if($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('phone.confirm') }}">
            @csrf

            <input
                name="otp"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                autocomplete="one-time-code"
                required
                autofocus
            >

            <button type="submit">Verify Phone</button>
        </form>

        <form method="POST" action="{{ route('phone.send') }}">
            @csrf
            <p>Didn't receive the code?</p>
            <button type="submit">Resend Code</button>
        </form>
    </main>
</body>
</html>