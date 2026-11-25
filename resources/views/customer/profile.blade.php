@extends('layouts.app')

@section('title', 'Profil')
@section('content')
    <section class="page-hero page-hero-small">
        <div class="container">
            <div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> AKUN SAYA</div>
            <h1>Profil</h1>
            <p>Perbarui informasi kontak dan alamat Anda.</p>
        </div>
    </section>

    <section class="section profile-section">
        <div class="container">
            <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('PUT')
                <label>Nama lengkap
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                </label>
                <label>Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                </label>
                <label>Nomor telepon
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                </label>
                <label class="profile-address">Alamat
                    <textarea name="address" rows="4" autocomplete="street-address">{{ old('address', $user->address) }}</textarea>
                </label>
                <button class="button button-dark" type="submit">Simpan perubahan</button>
            </form>
        </div>
    </section>
@endsection
