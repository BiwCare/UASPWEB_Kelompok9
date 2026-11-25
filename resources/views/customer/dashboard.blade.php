@extends('layouts.app')
@section('title', 'Pesanan saya')
@section('content')
    <section class="page-hero page-hero-small">
        <div class="container"><div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> RUANG PELANGGAN</div><h1>Halo, {{ auth()->user()->name }}.</h1><p>Kelola penyewaan dan pantau perjalanan alat Anda di sini.</p></div>
    </section>
    <section class="section dashboard-section">
        <div class="container">
            <div class="dashboard-title"><div><div class="eyebrow"><span class="eyebrow-dot"></span> RIWAYAT SEWA</div><h2>Pesanan saya</h2></div><a href="{{ route('catalog') }}" class="button button-dark">Sewa alat lagi</a></div>
            @forelse ($rentals as $rental)
                @php
                    $statuses = [
                        'menunggu_verifikasi' => 'Menunggu konfirmasi',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'sedang_disewa' => 'Sedang disewa',
                        'menunggu_pengembalian' => 'Menunggu pengembalian',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ];
                    $paymentStatus = ['belum_dibayar' => 'Belum dibayar', 'menunggu_verifikasi' => 'Menunggu verifikasi', 'lunas' => 'Lunas', 'ditolak' => 'Pembayaran ditolak'];
                @endphp
                <article class="rental-card">
                    <div class="rental-card-head"><div><small>NOMOR PESANAN</small><h3>{{ $rental->nomor_sewa }}</h3></div><span class="status-badge status-{{ $rental->status }}">{{ $statuses[$rental->status] ?? $rental->status }}</span></div>
                    <div class="rental-card-body">
                        <div class="rental-items">@foreach ($rental->items as $item)<div class="rental-item">@if ($item->peralatan->gambar)<img src="{{ str_starts_with($item->peralatan->gambar, 'equipment/') ? asset('storage/'.$item->peralatan->gambar) : asset($item->peralatan->gambar) }}" alt="">@else<span class="mini-image">🌱</span>@endif<span><strong>{{ $item->peralatan->nama }}</strong><small>{{ $item->unit?->kode_unit ?? 'Unit' }} · {{ $item->jumlah }} unit</small></span></div>@endforeach</div>
                        <div class="rental-facts"><div><small>JADWAL SEWA</small><strong>{{ $rental->tanggal_mulai->format('d M Y') }} – {{ $rental->tanggal_selesai->format('d M Y') }}</strong></div><div><small>LAMA SEWA</small><strong>{{ $rental->jumlah_hari }} hari</strong></div><div><small>TOTAL TAGIHAN</small><strong>Rp {{ number_format((float) $rental->total, 0, ',', '.') }}</strong><small>Termasuk deposit Rp {{ number_format((float) $rental->total_deposit, 0, ',', '.') }}</small></div></div>
                    </div>
                    <div class="rental-card-foot">
                        <div class="payment-state"><span class="status-badge status-{{ $rental->status_pembayaran }}">{{ $paymentStatus[$rental->status_pembayaran] ?? $rental->status_pembayaran }}</span>@if ($rental->pembayaran->isNotEmpty())<small>{{ ucfirst($rental->pembayaran->last()->metode) }}</small>@endif</div>
                        <div class="rental-actions">
                            @if ($rental->status_pembayaran !== 'lunas' && ! $rental->pembayaran->contains('status', 'menunggu_verifikasi'))
                                <details class="action-details"><summary class="button button-small button-lime">Kirim pembayaran</summary><form class="inline-action-form" method="POST" action="{{ route('payments.store', $rental) }}" enctype="multipart/form-data">@csrf<label>Metode<select name="metode" required><option value="transfer">Transfer bank</option><option value="tunai">Tunai</option></select></label><label>Nomor referensi <small>(opsional)</small><input type="text" name="referensi" maxlength="100"></label><label>Bukti transfer <small>(opsional)</small><input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.webp"></label><button class="button button-dark button-small">Kirim bukti</button></form></details>
                            @endif
                            @if ($rental->status === 'sedang_disewa')
                                <form method="POST" action="{{ route('returns.store', $rental) }}" class="inline-return-form">@csrf<input name="catatan" placeholder="Catatan pengembalian (opsional)"><button class="button button-small button-outline">Ajukan pengembalian</button></form>
                            @endif
                        </div>
                    </div>
                    @if ($rental->riwayatStatus->isNotEmpty())<details class="rental-history"><summary>Lihat riwayat status</summary><ol>@foreach ($rental->riwayatStatus as $history)<li><strong>{{ $statuses[$history->status] ?? $history->status }}</strong><small>{{ $history->created_at->format('d M Y, H:i') }}@if ($history->catatan) · {{ $history->catatan }}@endif</small></li>@endforeach</ol></details>@endif
                </article>
            @empty
                <div class="empty-state"><h3>Belum ada pesanan</h3><p>Jelajahi katalog untuk memilih alat yang Anda perlukan.</p><a href="{{ route('catalog') }}" class="button button-dark">Lihat katalog</a></div>
            @endforelse
        </div>
    </section>
@endsection
