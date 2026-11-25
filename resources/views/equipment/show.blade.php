@extends('layouts.app')

@section('title', $equipment->nama)
@section('content')
    @php
        $imageUrl = $equipment->gambar
            ? (str_starts_with($equipment->gambar, 'equipment/') ? asset('storage/'.$equipment->gambar) : asset($equipment->gambar))
            : null;
    @endphp
    <section class="page-hero page-hero-small">
        <div class="container"><a class="back-link" href="{{ route('catalog') }}">← Kembali ke katalog</a><div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> {{ $equipment->kategori->nama }}</div><h1>{{ $equipment->nama }}</h1><p>Unit terawat dan siap menemani pekerjaan Anda di lahan.</p></div>
    </section>
    <section class="section detail-section">
        <div class="container detail-grid">
            <div class="detail-gallery">
                <div class="detail-image">
                    @if ($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $equipment->nama }}">@else<span class="image-placeholder">🌾</span>@endif
                    <span class="image-label"><span class="availability-dot"></span> {{ $equipment->unit_tersedia_count }} unit tersedia</span>
                </div>
                <div class="detail-description">
                    <div class="eyebrow"><span class="eyebrow-dot"></span> TENTANG ALAT</div>
                    <h2>Siap untuk <em>kerja nyata.</em></h2>
                    <p>{{ $equipment->deskripsi }}</p>
                    @if ($equipment->spesifikasi)
                        <div class="spec-list">@foreach ($equipment->spesifikasi as $label => $value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</div>
                    @endif
                </div>
            </div>
            <aside class="booking-card">
                <div class="eyebrow"><span class="eyebrow-dot"></span> DETAIL SEWA</div>
                <div class="booking-price">Rp {{ number_format((float) $equipment->harga_per_hari, 0, ',', '.') }}<small> / unit / hari</small></div>
                <p class="booking-deposit">Deposit yang dapat dikembalikan: <strong>Rp {{ number_format((float) $equipment->deposit, 0, ',', '.') }}</strong> / unit</p>
                <div class="availability-note"><span class="availability-dot"></span><span><strong>{{ $equipment->unit_tersedia_count }} unit siap disewa</strong><small>Admin akan mengonfirmasi jadwal Anda.</small></span></div>
                @auth
                    @if (auth()->user()->isAdmin())
                        <div class="login-prompt"><p>Akun admin tidak dapat mengajukan pemesanan alat.</p><a href="{{ route('admin.dashboard') }}" class="button button-dark button-full">Kembali ke dashboard admin</a></div>
                    @else
                        <form method="POST" action="{{ route('bookings.store') }}" class="booking-form">
                            @csrf
                            <input type="hidden" name="peralatan_id" value="{{ $equipment->id }}">
                            <label>Tanggal mulai<input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required data-rent-start></label>
                            <label>Tanggal selesai<input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', now()->addDay()->toDateString()) }}" min="{{ now()->toDateString() }}" required data-rent-end></label>
                            <label>Jumlah unit<select name="jumlah" required>@for ($i = 1; $i <= min(10, max(1, $equipment->unit_tersedia_count)); $i++)<option value="{{ $i }}" @selected(old('jumlah') == $i)>{{ $i }} unit</option>@endfor</select></label>
                            <label>Alamat penggunaan<textarea name="alamat_pengiriman" rows="3" placeholder="Desa, kecamatan, kabupaten..." required>{{ old('alamat_pengiriman', auth()->user()->address) }}</textarea></label>
                            <label>Catatan tambahan <small>(opsional)</small><textarea name="catatan" rows="2" placeholder="Informasi akses lahan atau kebutuhan lain">{{ old('catatan') }}</textarea></label>
                            <div class="estimate-row"><span>Estimasi harga sewa</span><strong data-rent-estimate data-price="{{ $equipment->harga_per_hari }}">Rp {{ number_format((float) $equipment->harga_per_hari, 0, ',', '.') }}</strong></div>
                            <button class="button button-lime button-full" type="submit" @disabled($equipment->unit_tersedia_count < 1)>Ajukan pemesanan <span>↗</span></button>
                        </form>
                    @endif
                @else
                    <div class="login-prompt"><p>Masuk atau buat akun untuk mengajukan pemesanan alat ini.</p><a href="{{ route('login') }}" class="button button-lime button-full">Masuk untuk menyewa <span>↗</span></a><a href="{{ route('register') }}" class="text-link centered">Belum punya akun? Daftar</a></div>
                @endauth
                <p class="booking-footnote">Harga belum termasuk biaya pengiriman (jika ada). Tim kami akan menghubungi Anda untuk konfirmasi.</p>
            </aside>
        </div>
    </section>
@endsection
