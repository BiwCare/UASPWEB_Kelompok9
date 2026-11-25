@php
    $imageUrl = $equipment->gambar
        ? (str_starts_with($equipment->gambar, 'equipment/') ? asset('storage/'.$equipment->gambar) : asset($equipment->gambar))
        : null;
@endphp
<article class="equipment-card">
    <a class="equipment-image" href="{{ route('equipment.show', $equipment->slug) }}">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $equipment->nama }}" loading="lazy">
        @else
            <span class="image-placeholder">🌾</span>
        @endif
        @if ($equipment->unggulan)<span class="image-label">Pilihan petani</span>@endif
    </a>
    <div class="equipment-info">
        <div class="equipment-meta"><span>{{ $equipment->kategori->nama }}</span><span class="availability-dot">{{ $equipment->unit_tersedia_count ?? 0 }} unit siap</span></div>
        <h3><a href="{{ route('equipment.show', $equipment->slug) }}">{{ $equipment->nama }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit($equipment->deskripsi, 86) }}</p>
        <div class="equipment-card-bottom">
            <div class="price">Rp {{ number_format((float) $equipment->harga_per_hari, 0, ',', '.') }}<small> / hari</small></div>
            <a class="round-link" href="{{ route('equipment.show', $equipment->slug) }}" aria-label="Lihat {{ $equipment->nama }}">↗</a>
        </div>
    </div>
</article>
