<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM Center</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body class="login-body">
    <div class="login-orbs" aria-hidden="true">
        <span class="login-orb login-orb-one"></span>
        <span class="login-orb login-orb-two"></span>
        <span class="login-orb login-orb-three"></span>
    </div>

    <div class="login-card">
        <h1>Sign In</h1>
        <p>Sign in to your CRM Center account</p>

        @if (session('status'))
            <div class="form-alert form-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="********" required>

            <label class="login-checkbox">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>

            <button type="submit">Sign In</button>
        </form>

        <div class="login-footer">
            <div class="login-links">
                <a href="{{ route('password.request') }}">Forgot password?</a>
                <!-- <a href="{{ route('register') }}">Create a new account</a> -->
            </div>
        </div>
    </div>
</body>
</html>
