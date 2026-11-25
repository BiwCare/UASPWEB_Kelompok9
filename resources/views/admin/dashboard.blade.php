@extends('layouts.app')
@section('title', 'Dashboard admin')
@section('content')
    <section class="page-hero page-hero-small admin-hero">
        <div class="container"><div class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> PANEL PENGELOLAAN</div><h1>Selamat bekerja, {{ auth()->user()->name }}.</h1><p>Kelola pesanan, peralatan, pembayaran, dan kondisi unit dalam satu tempat.</p></div>
    </section>
    <section class="admin-section">
        <div class="container">
            <nav class="admin-nav">
                <a href="#ringkasan">Ringkasan</a><a href="#pesanan">Pesanan</a><a href="#pembayaran">Pembayaran</a><a href="#pengembalian">Pengembalian</a><a href="#inventaris">Inventaris</a><a href="#servis">Servis</a><a href="#konfigurasi">Konfigurasi</a>
            </nav>
            <div class="admin-stats" id="ringkasan">
                <article><span>SEMUA PESANAN</span><strong>{{ number_format($stats['orders']) }}</strong><small>Riwayat transaksi tersimpan</small></article>
                <article><span>PERLU DITINJAU</span><strong>{{ number_format($stats['pending']) }}</strong><small>Menunggu keputusan admin</small></article>
                <article><span>ALAT AKTIF</span><strong>{{ number_format($stats['equipment']) }}</strong><small>Jenis alat dalam katalog</small></article>
                <article><span>UNIT TERSEDIA</span><strong>{{ number_format($stats['units']) }}</strong><small>Siap dipakai pelanggan</small></article>
            </div>

            <section class="admin-panel" id="pesanan">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> OPERASIONAL</div><h2>Pesanan <em>terbaru.</em></h2></div><div class="admin-row-actions"><span class="panel-count">{{ $rentals->count() }} terakhir</span><a class="button button-small button-dark" href="{{ route('admin.rentals.export') }}">Unduh CSV (Excel)</a></div></div>
                @forelse ($rentals as $rental)
                    @php
                        $statusLabels = ['menunggu_verifikasi' => 'Menunggu verifikasi', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'sedang_disewa' => 'Sedang disewa', 'menunggu_pengembalian' => 'Menunggu pengembalian', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
                        $nextStatuses = ['menunggu_verifikasi' => ['disetujui' => 'Setujui', 'ditolak' => 'Tolak'], 'disetujui' => ['sedang_disewa' => 'Mulai disewa', 'dibatalkan' => 'Batalkan']];
                    @endphp
                    <article class="admin-rental-row">
                        <div class="admin-rental-summary">
                            <div><small>{{ $rental->nomor_sewa }} · {{ $rental->created_at->format('d M Y') }}</small><h3>{{ $rental->user->name }} <span>{{ $rental->user->phone }}</span></h3><div class="admin-rental-tools">@foreach ($rental->items as $item)<span>{{ $item->peralatan->nama }} · {{ $item->unit?->kode_unit }}</span>@endforeach</div></div>
                            <div class="admin-rental-right"><span class="status-badge status-{{ $rental->status }}">{{ $statusLabels[$rental->status] ?? $rental->status }}</span><strong>Rp {{ number_format((float) $rental->total, 0, ',', '.') }}</strong><small>{{ $rental->tanggal_mulai->format('d M') }} – {{ $rental->tanggal_selesai->format('d M Y') }}</small></div>
                        </div>
                        @if (!empty($nextStatuses[$rental->status]))
                            <form method="POST" action="{{ route('admin.rentals.update', $rental) }}" class="admin-inline-form">@csrf @method('PATCH')<select name="status" required><option value="">Pilih tindakan</option>@foreach ($nextStatuses[$rental->status] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><input name="catatan" placeholder="Catatan untuk riwayat (opsional)"><button class="button button-small button-dark">Simpan status</button></form>
                        @endif
                    </article>
                @empty
                    <div class="admin-empty">Belum ada pesanan masuk.</div>
                @endforelse
            </section>

            <section class="admin-panel" id="pembayaran">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> VERIFIKASI</div><h2>Pembayaran <em>masuk.</em></h2></div><span class="panel-count">{{ $payments->count() }} menunggu</span></div>
                @forelse ($payments as $payment)
                    <article class="admin-simple-row">
                        <div><small>{{ $payment->transaksiSewa->nomor_sewa }} · {{ $payment->created_at->format('d M Y, H:i') }}</small><h3>{{ $payment->transaksiSewa->user->name }}</h3><p>{{ ucfirst($payment->metode) }} · Rp {{ number_format((float) $payment->jumlah, 0, ',', '.') }} @if($payment->referensi)· Ref {{ $payment->referensi }}@endif</p></div>
                        <div class="admin-row-actions">
                            @if ($payment->bukti_pembayaran)<a class="button button-small button-outline" href="{{ route('admin.payments.proof', $payment) }}">Unduh bukti</a>@endif
                            <form method="POST" action="{{ route('admin.payments.update', $payment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="diterima"><button class="button button-small button-lime">Terima</button></form>
                            <form method="POST" action="{{ route('admin.payments.update', $payment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="ditolak"><button class="button button-small button-outline">Tolak</button></form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty">Tidak ada pembayaran yang menunggu verifikasi.</div>
                @endforelse
            </section>

            <section class="admin-panel" id="pengembalian">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> PEMERIKSAAN UNIT</div><h2>Pengembalian <em>alat.</em></h2></div><span class="panel-count">{{ $returns->count() }} perlu diperiksa</span></div>
                @forelse ($returns as $return)
                    <article class="admin-simple-row return-admin-row">
                        <div><small>{{ $return->transaksiSewa->nomor_sewa }} · {{ $return->tanggal_kembali?->format('d M Y, H:i') }}</small><h3>{{ $return->transaksiSewa->user->name }}</h3><p>@foreach ($return->transaksiSewa->items as $item){{ $item->peralatan->nama }} ({{ $item->unit?->kode_unit }})@if(!$loop->last), @endif @endforeach</p><p>{{ $return->catatan ?: 'Tidak ada catatan pelanggan.' }}</p></div>
                        <form method="POST" action="{{ route('admin.returns.complete', $return) }}" class="return-review-form">@csrf @method('PATCH')<label>Kondisi<select name="kondisi"><option value="baik">Baik</option><option value="perlu_servis">Perlu servis</option><option value="rusak">Rusak</option></select></label><label>Denda <small>(otomatis sesuai keterlambatan jika kosong)</small><input type="number" name="denda" min="0" step="1000" placeholder="Hitung otomatis"></label><label>Catatan<input name="catatan" placeholder="Temuan inspeksi"></label><button class="button button-small button-dark">Selesaikan & periksa</button></form>
                    </article>
                @empty
                    <div class="admin-empty">Belum ada pengajuan pengembalian.</div>
                @endforelse
            </section>

            <section class="admin-panel" id="inventaris">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> KATALOG & UNIT</div><h2>Kelola <em>inventaris.</em></h2></div></div>
                <div class="admin-forms-grid">
                    <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-form-card">@csrf<h3>Tambah kategori</h3><label>Nama kategori<input name="nama" maxlength="100" placeholder="Contoh: Pengolahan lahan" required></label><label>Deskripsi<textarea name="deskripsi" rows="2"></textarea></label><button class="button button-small button-dark">Simpan kategori</button></form>
                    <form method="POST" action="{{ route('admin.equipment.store') }}" class="admin-form-card" enctype="multipart/form-data">@csrf<h3>Tambah alat baru</h3><label>Kategori<select name="kategori_peralatan_id" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nama }}</option>@endforeach</select></label><label>Nama alat<input name="nama" maxlength="150" required></label><div class="form-two-col"><label>Merek<input name="merek" maxlength="100"></label><label>Jumlah unit<input type="number" name="jumlah_unit" min="1" max="100" value="1" required></label></div><div class="form-two-col"><label>Harga / hari<input type="number" name="harga_per_hari" min="1" step="1000" required></label><label>Deposit / unit<input type="number" name="deposit" min="0" step="1000" value="0" required></label></div><label>Deskripsi<textarea name="deskripsi" rows="2"></textarea></label><label>Foto alat<input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp"></label><button class="button button-small button-lime">Tambah alat & unit</button></form>
                    <form method="POST" action="{{ route('admin.units.store') }}" class="admin-form-card">@csrf<h3>Tambah unit inventaris</h3><label>Jenis alat<select name="peralatan_id" required><option value="">Pilih alat</option>@foreach($equipment as $item)<option value="{{ $item->id }}">{{ $item->nama }}</option>@endforeach</select></label><label>Kode unit<input name="kode_unit" maxlength="50" placeholder="Contoh: TRAKTOR-003" required></label><button class="button button-small button-dark">Tambah unit</button></form>
                </div>
                <div class="inventory-list">
                    @foreach($equipment as $item)
                        <div class="inventory-row"><span><strong>{{ $item->nama }}</strong><small>{{ $item->kategori->nama }} · {{ $item->units_count }} unit · Rp {{ number_format((float) $item->harga_per_hari, 0, ',', '.') }}/hari</small></span><form method="POST" action="{{ route('admin.equipment.toggle', $item) }}">@csrf @method('PATCH')<button class="status-badge {{ $item->aktif ? 'status-tersedia' : 'status-ditolak' }}">{{ $item->aktif ? 'Aktif · Nonaktifkan' : 'Nonaktif · Aktifkan' }}</button></form></div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel" id="servis">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> PERAWATAN ALAT</div><h2>Catatan <em>servis.</em></h2></div></div>
                <div class="service-section-grid">
                    <form method="POST" action="{{ route('admin.services.store') }}" class="admin-form-card">@csrf<h3>Jadwalkan servis</h3><label>Unit alat<select name="unit_peralatan_id" required><option value="">Pilih unit</option>@foreach($allUnits as $unit)<option value="{{ $unit->id }}">{{ $unit->peralatan->nama }} · {{ $unit->kode_unit }} ({{ $unit->status }})</option>@endforeach</select></label><label>Judul servis<input name="judul" maxlength="150" required></label><label>Deskripsi<textarea name="deskripsi" rows="2"></textarea></label><label>Estimasi biaya<input type="number" name="biaya" min="0" step="1000" value="0" required></label><button class="button button-small button-dark">Simpan jadwal</button></form>
                    <div class="service-list">
                        @forelse($services as $service)
                            <article class="service-row"><div><span class="status-badge status-{{ $service->status }}">{{ $service->status === 'selesai' ? 'Selesai' : 'Dijadwalkan' }}</span><h3>{{ $service->judul }}</h3><p>{{ $service->unit->peralatan->nama }} · {{ $service->unit->kode_unit }}</p><small>{{ $service->deskripsi }} · Rp {{ number_format((float) $service->biaya, 0, ',', '.') }}</small></div>@if($service->status !== 'selesai')<form method="POST" action="{{ route('admin.services.finish', $service) }}">@csrf @method('PATCH')<button class="button button-small button-outline">Tandai selesai</button></form>@endif</article>
                        @empty
                            <div class="admin-empty">Belum ada catatan servis.</div>
                        @endforelse
                    </div>
                </div>
                @if($serviceUnits->isNotEmpty())<div class="service-warning"><strong>{{ $serviceUnits->count() }} unit perlu pemeriksaan:</strong> @foreach($serviceUnits as $unit){{ $unit->kode_unit }}@if(!$loop->last), @endif @endforeach</div>@endif
            </section>

            <section class="admin-panel" id="konfigurasi">
                <div class="admin-panel-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> IDENTITAS WEBSITE</div><h2>Konfigurasi <em>usaha.</em></h2></div></div>
                <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">@csrf @method('PUT')
                    <label>Nama usaha<input name="nama_usaha" value="{{ $settings['nama_usaha'] ?? 'SawahKita' }}" maxlength="120" required></label>
                    <label>Tagline<input name="tagline" value="{{ $settings['tagline'] ?? '' }}" maxlength="180" required></label>
                    <label>Nomor WhatsApp<input name="nomor_whatsapp" value="{{ $settings['nomor_whatsapp'] ?? '' }}" maxlength="30"></label>
                    <label>Alamat usaha<input name="alamat_usaha" value="{{ $settings['alamat_usaha'] ?? '' }}" maxlength="500"></label>
                    <label>Denda keterlambatan per hari<input type="number" name="biaya_keterlambatan_harian" value="{{ $settings['biaya_keterlambatan_harian'] ?? 0 }}" min="0" step="1000" required></label>
                    <button class="button button-dark">Simpan konfigurasi <span>↗</span></button>
                </form>
            </section>
        </div>
    </section>
@endsection
