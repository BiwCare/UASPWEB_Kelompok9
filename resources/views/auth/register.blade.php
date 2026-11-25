@extends('layouts.app')
@section('title', 'Daftar akun')
@section('content')
    <section class="auth-page">
        <div class="auth-aside"><div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> TUMBUH BERSAMA SAWAHKITA</div><h1>Alat yang tepat<br>ada <em>di sini.</em></h1><p>Buat akun gratis untuk memesan alat, mengirim bukti pembayaran, dan memantau status sewa Anda.</p></div>
        <div class="auth-panel"><a class="back-link" href="{{ route('home') }}">← Kembali ke beranda</a><div class="eyebrow"><span class="eyebrow-dot"></span> MULAI DI SINI</div><h2>Buat akun baru.</h2><p>Hanya butuh beberapa informasi.</p>
            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                @csrf
                <label>Nama lengkap<input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" autocomplete="name" required></label>
                <label>Email<input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required></label>
                <label>Nomor telepon<input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xx xxxx xxxx" autocomplete="tel" required></label>
                <label>Kata sandi<input type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required></label>
                <label>Ulangi kata sandi<input type="password" name="password_confirmation" placeholder="Ketik ulang kata sandi" autocomplete="new-password" required></label>
                <button class="button button-dark button-full" type="submit">Buat akun <span>↗</span></button>
            </form>
            <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </div>
    </section>
@endsection
