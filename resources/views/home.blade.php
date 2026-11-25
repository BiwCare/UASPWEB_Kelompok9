@extends('layouts.app')

@section('title', 'Sewa alat pertanian')
@section('content')
    <section class="home-hero">
        <img class="home-hero-image" src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&amp;fit=crop&amp;w=2000&amp;q=90" alt="" aria-hidden="true">
        <div class="home-hero-overlay"></div>
        <div class="container home-hero-content">
            <p class="home-kicker">SEWA ALAT PERTANIAN</p>
            <h1>Alat yang tepat<br>untuk lahan Anda.</h1>
            <p class="home-description">Pilih alat tani yang Anda butuhkan, tentukan tanggal sewa, dan pesan langsung secara online.</p>
            <a href="{{ route('catalog') }}" class="button button-lime">Lihat katalog</a>
        </div>
    </section>
@endsection
