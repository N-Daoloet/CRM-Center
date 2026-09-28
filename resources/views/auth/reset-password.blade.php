<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - CRM Center</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>Password Baru</h1>
        <p>Masukkan password baru untuk akun Anda</p>

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="nama@email.com" required>

            <label for="password">Password Baru</label>
            <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>

            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password baru" required>

            <button type="submit">Simpan Password</button>
        </form>

        <div class="login-footer">
            <a href="{{ route('login') }}">Kembali ke login</a>
        </div>
    </div>
    @include('partials.form-confirmation')
</body>
</html>
