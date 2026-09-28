<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CRM Center</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>Daftar</h1>
        <p>Buat akun baru untuk mengakses CRM Center</p>

        @if ($errors->any())
            <div class="form-alert form-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama lengkap" required>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>

            <label for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password" required>

            <button type="submit">Buat Akun</button>
        </form>

        <div class="login-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>
    @include('partials.form-confirmation')
</body>
</html>
