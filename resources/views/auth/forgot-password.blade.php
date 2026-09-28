<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CRM Center</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>Reset Password</h1>
        <p>Masukkan email akun Anda untuk menerima tautan reset password</p>

        @if (session('status'))
            <div class="form-alert form-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required>

            <button type="submit">Kirim Link Reset</button>
        </form>

        <div class="login-footer">
            <a href="{{ route('login') }}">Kembali ke login</a>
        </div>
    </div>
    @include('partials.form-confirmation')
</body>
</html>
