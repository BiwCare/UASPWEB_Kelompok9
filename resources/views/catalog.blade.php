@extends('layouts.app')

@section('title', 'Katalog alat')
@section('content')
    <section class="catalog-page">
        <div class="container">
            <header class="catalog-page-heading">
                <p class="eyebrow"><span class="eyebrow-dot"></span> SAWAHKITA</p>
                <h1>Katalog alat</h1>
                <p>Pilih alat dan tanggal penggunaan untuk melihat ketersediaan unit.</p>
            </header>

            <form method="GET" action="{{ route('catalog') }}" class="catalog-toolbar">
                <label class="search-box">
                    <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau merek alat">
                    <button type="submit">Cari</button>
                </label>
                <nav class="filter-pills" aria-label="Filter kategori">
                    <a href="{{ route('catalog', request()->except('kategori', 'page')) }}" class="{{ request('kategori') ? '' : 'selected' }}">Semua</a>
                    @foreach ($categories as $category)
                        <a href="{{ route('catalog', array_filter([...request()->except('page'), 'kategori' => $category->slug])) }}" class="{{ request('kategori') === $category->slug ? 'selected' : '' }}">{{ $category->nama }}</a>
                    @endforeach
                </nav>
            </form>

            @if ($equipment->isNotEmpty())
                <div class="equipment-grid">
                    @foreach ($equipment as $item)
                        <x-equipment-card :equipment="$item" />
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $equipment->links() }}</div>
            @else
                <div class="empty-state">
                    <h2>Alat tidak ditemukan</h2>
                    <p>Coba ubah kata pencarian atau pilih kategori lain.</p>
                    <a class="button button-dark" href="{{ route('catalog') }}">Lihat semua alat</a>
                </div>
            @endif
        </div>
    </section>
@endsection
