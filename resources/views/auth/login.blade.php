@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
    <section class="auth-page">
        <div class="auth-aside"><div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> SENANG MELIHAT ANDA LAGI</div><h1>Musim baru,<br><em>peluang baru.</em></h1><p>Masuk untuk mengatur pemesanan</p></div>
        <div class="auth-panel"><a class="back-link" href="{{ route('home') }}">← Kembali ke beranda</a><div class="eyebrow"><span class="eyebrow-dot"></span> AKUN SAWAHKITA</div><h2>Selamat datang.</h2><p>Masukkan email dan kata sandi Anda.</p>
            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf
                <label>Email<input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus></label>
                <label>Kata sandi<input type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required></label>
                <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
                <button class="button button-dark button-full" type="submit">Masuk ke akun <span>↗</span></button>
            </form>
            <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a></p>
        </div>
    </section>
@endsection
