<?php

namespace Database\Seeders;

use App\Models\KategoriPeralatan;
use App\Models\KonfigurasiSistem;
use App\Models\Peralatan;
use App\Models\UnitPeralatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama' => 'Olah Lahan', 'slug' => 'olah-lahan', 'deskripsi' => 'Peralatan tangguh untuk menyiapkan lahan tanam.', 'ikon' => 'field'],
            ['nama' => 'Penanaman', 'slug' => 'penanaman', 'deskripsi' => 'Solusi tanam yang lebih cepat dan presisi.', 'ikon' => 'seedling'],
            ['nama' => 'Panen', 'slug' => 'panen', 'deskripsi' => 'Bantu panen lebih efisien dan hemat tenaga.', 'ikon' => 'harvest'],
            ['nama' => 'Pengairan', 'slug' => 'pengairan', 'deskripsi' => 'Jaga aliran air untuk lahan dan tanaman.', 'ikon' => 'water'],
            ['nama' => 'Perawatan Tanaman', 'slug' => 'perawatan-tanaman', 'deskripsi' => 'Rawat tanaman dengan alat yang tepat.', 'ikon' => 'care'],
        ];

        foreach ($categories as $category) {
            KategoriPeralatan::updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'aktif' => true],
            );
        }

        $equipment = [
            ['nama' => 'Traktor Roda Dua', 'kategori' => 'olah-lahan', 'merek' => 'Yanmar', 'harga_per_hari' => 275000, 'deposit' => 300000, 'gambar' => 'images/equipment/traktor.jpg', 'unggulan' => true, 'deskripsi' => 'Traktor tangan bertenaga untuk membajak dan menggemburkan sawah maupun kebun. Mudah dikendalikan dan siap kerja di berbagai kondisi lahan.', 'spesifikasi' => ['Tipe' => 'Roda dua', 'Bahan bakar' => 'Diesel', 'Penggunaan' => 'Bajak dan olah tanah']],
            ['nama' => 'Mesin Panen Padi', 'kategori' => 'panen', 'merek' => 'Kubota', 'harga_per_hari' => 650000, 'deposit' => 500000, 'gambar' => 'images/equipment/mesin-panen-padi.jpg', 'unggulan' => true, 'deskripsi' => 'Mesin panen padi praktis untuk memotong dan mengumpulkan hasil panen dengan lebih cepat. Cocok untuk musim panen yang singkat.', 'spesifikasi' => ['Tipe' => 'Mini combine harvester', 'Penggunaan' => 'Panen padi', 'Operator' => '1 orang']],
            ['nama' => 'Mesin Tanam Padi', 'kategori' => 'penanaman', 'merek' => 'Andaro', 'harga_per_hari' => 550000, 'deposit' => 500000, 'gambar' => 'images/equipment/mesin-tanam-padi.jpg', 'unggulan' => true, 'deskripsi' => 'Rice transplanter untuk membantu penanaman bibit padi yang rapi dan seragam dengan waktu kerja lebih efisien.', 'spesifikasi' => ['Tipe' => 'Rice transplanter', 'Penggunaan' => 'Tanam padi', 'Kapasitas' => 'Multi-baris']],
            ['nama' => 'Pompa Air Bensin', 'kategori' => 'pengairan', 'merek' => 'Lonfa', 'harga_per_hari' => 125000, 'deposit' => 150000, 'gambar' => 'images/equipment/pompa-air.jpg', 'unggulan' => false, 'deskripsi' => 'Pompa air portabel dengan rangka pelindung yang kokoh untuk membantu irigasi dan menguras genangan di lahan.', 'spesifikasi' => ['Tipe' => 'Pompa sentrifugal', 'Bahan bakar' => 'Bensin', 'Penggunaan' => 'Irigasi dan drainase']],
            ['nama' => 'Kultivator Mini', 'kategori' => 'olah-lahan', 'merek' => 'Honda', 'harga_per_hari' => 175000, 'deposit' => 200000, 'gambar' => 'images/equipment/kultivator-mini.jpg', 'unggulan' => false, 'deskripsi' => 'Kultivator ringkas untuk menggemburkan tanah, membuat bedengan, dan merawat kebun sayur.', 'spesifikasi' => ['Tipe' => 'Kultivator mini', 'Bahan bakar' => 'Bensin', 'Penggunaan' => 'Kebun dan bedengan']],
            ['nama' => 'Sprayer Punggung', 'kategori' => 'perawatan-tanaman', 'merek' => 'Tiger', 'harga_per_hari' => 85000, 'deposit' => 100000, 'gambar' => 'images/equipment/sprayer.jpg', 'unggulan' => false, 'deskripsi' => 'Sprayer punggung bermesin untuk penyemprotan tanaman yang merata dan menjangkau area kebun lebih luas.', 'spesifikasi' => ['Tipe' => 'Sprayer bermesin', 'Kapasitas' => '20 liter', 'Penggunaan' => 'Penyemprotan tanaman']],
        ];

        foreach ($equipment as $item) {
            $category = KategoriPeralatan::where('slug', $item['kategori'])->firstOrFail();
            $slug = Str::slug($item['nama']);
            $peralatan = Peralatan::updateOrCreate(
                ['slug' => $slug],
                [
                    'kategori_peralatan_id' => $category->id,
                    'nama' => $item['nama'],
                    'merek' => $item['merek'],
                    'harga_per_hari' => $item['harga_per_hari'],
                    'deposit' => $item['deposit'],
                    'gambar' => $item['gambar'],
                    'unggulan' => $item['unggulan'],
                    'deskripsi' => $item['deskripsi'],
                    'spesifikasi' => $item['spesifikasi'],
                    'aktif' => true,
                ],
            );

            foreach ([1, 2] as $unitNumber) {
                UnitPeralatan::firstOrCreate(
                    ['kode_unit' => Str::upper(Str::slug($peralatan->nama)).'-'.str_pad((string) $unitNumber, 3, '0', STR_PAD_LEFT)],
                    [
                        'peralatan_id' => $peralatan->id,
                        'status' => 'tersedia',
                        'kondisi' => 'baik',
                        'tanggal_pengadaan' => now()->toDateString(),
                    ],
                );
            }
        }

        $settings = [
            'nama_usaha' => ['nilai' => 'SawahKita', 'grup' => 'umum'],
            'tagline' => ['nilai' => 'Alat tepat, panen lebih hebat.', 'grup' => 'umum'],
            'nomor_whatsapp' => ['nilai' => '6281234567890', 'grup' => 'kontak'],
            'alamat_usaha' => ['nilai' => 'Melayani petani di seluruh Indonesia', 'grup' => 'kontak'],
            'biaya_keterlambatan_harian' => ['nilai' => '50000', 'grup' => 'penyewaan', 'tipe' => 'number'],
        ];

        foreach ($settings as $key => $setting) {
            KonfigurasiSistem::updateOrCreate(
                ['kunci' => $key],
                ['nilai' => $setting['nilai'], 'grup' => $setting['grup'], 'tipe' => $setting['tipe'] ?? 'text'],
            );
        }
    }
}
