<?php

namespace App\Http\Controllers;

use App\Models\InspeksiUnit;
use App\Models\KategoriPeralatan;
use App\Models\KonfigurasiSistem;
use App\Models\PembayaranSewa;
use App\Models\PengembalianSewa;
use App\Models\Peralatan;
use App\Models\RiwayatStatusSewa;
use App\Models\ServisUnit;
use App\Models\TransaksiSewa;
use App\Models\UnitPeralatan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'orders' => TransaksiSewa::count(),
                'pending' => TransaksiSewa::where('status', 'menunggu_verifikasi')->count(),
                'equipment' => Peralatan::where('aktif', true)->count(),
                'units' => UnitPeralatan::where('status', 'tersedia')->count(),
            ],
            'rentals' => TransaksiSewa::with(['user', 'items.peralatan', 'items.unit', 'pembayaran', 'pengembalian'])
                ->latest()->take(30)->get(),
            'payments' => PembayaranSewa::with('transaksiSewa.user')->where('status', 'menunggu_verifikasi')->latest()->get(),
            'returns' => PengembalianSewa::with('transaksiSewa.items.unit')
                ->where('status', 'diajukan')->latest()->get(),
            'categories' => KategoriPeralatan::where('aktif', true)->orderBy('nama')->get(),
            'equipment' => Peralatan::with('kategori')->withCount('units')->latest()->get(),
            'serviceUnits' => UnitPeralatan::with('peralatan')->where('status', 'servis')->latest()->get(),
            'allUnits' => UnitPeralatan::with('peralatan')->whereIn('status', ['tersedia', 'servis'])->orderBy('kode_unit')->get(),
            'services' => ServisUnit::with('unit.peralatan')->latest()->take(8)->get(),
        ]);
    }

    public function exportRentals(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                throw new RuntimeException('Tidak dapat membuka aliran ekspor penyewaan.');
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, [
                'Nomor sewa',
                'Tanggal pesan',
                'Nama pelanggan',
                'Email',
                'Telepon',
                'Tanggal mulai',
                'Tanggal selesai',
                'Durasi (hari)',
                'Nama alat',
                'Kode unit',
                'Jumlah unit',
                'Harga per hari',
                'Subtotal item',
                'Total deposit',
                'Denda',
                'Total tagihan',
                'Status sewa',
                'Status pembayaran',
                'Metode pembayaran',
                'Jumlah pembayaran terakhir',
                'Status pengembalian',
                'Tanggal kembali',
                'Kondisi pengembalian',
                'Alamat penggunaan',
                'Catatan',
            ], ';', '"', '', "\r\n");

            TransaksiSewa::query()
                ->with([
                    'user',
                    'items.peralatan',
                    'items.unit',
                    'pembayaran' => fn ($query) => $query->latest('id'),
                    'pengembalian',
                ])
                ->chunkById(200, function (Collection $rentals) use ($stream): void {
                    foreach ($rentals as $rental) {
                        $latestPayment = $rental->pembayaran->first();
                        $rentalItems = $rental->items->isNotEmpty() ? $rental->items : [null];

                        foreach ($rentalItems as $item) {
                            $row = [
                                $rental->nomor_sewa,
                                $rental->created_at?->format('d/m/Y H:i'),
                                $rental->user->name,
                                $rental->user->email,
                                $rental->user->phone,
                                $rental->tanggal_mulai->format('d/m/Y'),
                                $rental->tanggal_selesai->format('d/m/Y'),
                                $rental->jumlah_hari,
                                $item?->peralatan?->nama,
                                $item?->unit?->kode_unit,
                                $item?->jumlah,
                                $item === null ? '' : number_format((float) $item->harga_harian, 2, ',', ''),
                                $item === null ? '' : number_format((float) $item->subtotal, 2, ',', ''),
                                number_format((float) $rental->total_deposit, 2, ',', ''),
                                number_format((float) $rental->denda, 2, ',', ''),
                                number_format((float) $rental->total, 2, ',', ''),
                                $rental->status,
                                $rental->status_pembayaran,
                                $latestPayment?->metode,
                                $latestPayment === null ? '' : number_format((float) $latestPayment->jumlah, 2, ',', ''),
                                $rental->pengembalian?->status,
                                $rental->pengembalian?->tanggal_kembali?->format('d/m/Y H:i'),
                                $rental->pengembalian?->kondisi,
                                $rental->alamat_pengiriman,
                                $rental->catatan,
                            ];

                            fputcsv($stream, array_map($this->safeCsvCell(...), $row), ';', '"', '', "\r\n");
                        }
                    }
                });

            fclose($stream);
        }, 'data-penyewaan-'.now()->format('Ymd').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function safeCsvCell(mixed $value): string
    {
        $cell = (string) ($value ?? '');

        return preg_match('/^[\x00-\x20]*[=+\-@]/', $cell) === 1
            ? "'".$cell
            : $cell;
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ]);

        $slug = Str::slug($data['nama']);
        abort_if(KategoriPeralatan::where('slug', $slug)->exists(), 422, 'Kategori dengan nama tersebut sudah ada.');

        KategoriPeralatan::create([
            ...$data,
            'slug' => $slug,
            'aktif' => true,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function storeEquipment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori_peralatan_id' => ['required', 'exists:kategori_peralatan,id'],
            'nama' => ['required', 'string', 'max:150'],
            'merek' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'harga_per_hari' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'deposit' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'jumlah_unit' => ['required', 'integer', 'min:1', 'max:100'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slug = Str::slug($data['nama']);
        abort_if(Peralatan::where('slug', $slug)->exists(), 422, 'Alat dengan nama tersebut sudah ada.');

        $equipment = DB::transaction(function () use ($data, $slug, $request): Peralatan {
            $equipment = Peralatan::create([
                'kategori_peralatan_id' => $data['kategori_peralatan_id'],
                'nama' => $data['nama'],
                'slug' => $slug,
                'merek' => $data['merek'] ?? null,
                'deskripsi' => $data['deskripsi'] ?? null,
                'harga_per_hari' => $data['harga_per_hari'],
                'deposit' => $data['deposit'],
                'gambar' => $request->file('gambar')?->storePublicly('equipment', 'public'),
                'aktif' => true,
            ]);

            for ($unitNumber = 1; $unitNumber <= (int) $data['jumlah_unit']; $unitNumber++) {
                UnitPeralatan::create([
                    'peralatan_id' => $equipment->id,
                    'kode_unit' => Str::upper(Str::slug($equipment->nama)).'-'.Str::upper(Str::random(5)).'-'.str_pad((string) $unitNumber, 3, '0', STR_PAD_LEFT),
                    'status' => 'tersedia',
                    'kondisi' => 'baik',
                ]);
            }

            return $equipment;
        });

        return back()->with('success', "{$equipment->nama} dan unit inventaris berhasil ditambahkan.");
    }

    public function toggleEquipment(Peralatan $peralatan): RedirectResponse
    {
        $peralatan->update(['aktif' => ! $peralatan->aktif]);

        return back()->with('success', 'Status alat berhasil diperbarui.');
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'peralatan_id' => ['required', 'exists:peralatan,id'],
            'kode_unit' => ['required', 'string', 'max:50', 'unique:unit_peralatan,kode_unit'],
        ]);

        UnitPeralatan::create([...$data, 'status' => 'tersedia', 'kondisi' => 'baik']);

        return back()->with('success', 'Unit inventaris berhasil ditambahkan.');
    }

    public function updateRental(Request $request, TransaksiSewa $transaksiSewa): RedirectResponse
    {
        $allowedStatuses = match ($transaksiSewa->status) {
            'menunggu_verifikasi' => ['disetujui', 'ditolak'],
            'disetujui' => ['sedang_disewa', 'dibatalkan'],
            default => [],
        };

        $data = $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request, $transaksiSewa): void {
            $transaksiSewa->update(['status' => $data['status']]);
            RiwayatStatusSewa::create([
                'transaksi_sewa_id' => $transaksiSewa->id,
                'user_id' => $request->user()->id,
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]);
        });

        return back()->with('success', 'Status pemesanan berhasil diperbarui.');
    }

    public function reviewPayment(Request $request, PembayaranSewa $pembayaranSewa): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['diterima', 'ditolak'])],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $pembayaranSewa): void {
            $pembayaranSewa->update([
                'status' => $data['status'],
                'dibayar_pada' => $data['status'] === 'diterima' ? now() : null,
                'catatan' => $data['catatan'] ?? null,
            ]);
            $pembayaranSewa->transaksiSewa->update([
                'status_pembayaran' => $data['status'] === 'diterima' ? 'lunas' : 'ditolak',
            ]);
        });

        return back()->with('success', 'Pembayaran berhasil diperiksa.');
    }

    public function downloadProof(PembayaranSewa $pembayaranSewa)
    {
        abort_unless($pembayaranSewa->bukti_pembayaran && Storage::exists($pembayaranSewa->bukti_pembayaran), 404);

        return Storage::download($pembayaranSewa->bukti_pembayaran);
    }

    public function completeReturn(Request $request, PengembalianSewa $pengembalianSewa): RedirectResponse
    {
        $data = $request->validate([
            'kondisi' => ['required', Rule::in(['baik', 'perlu_servis', 'rusak'])],
            'denda' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request, $pengembalianSewa): void {
            $rental = $pengembalianSewa->transaksiSewa()->with('items.unit')->lockForUpdate()->firstOrFail();
            abort_unless($pengembalianSewa->status === 'diajukan', 422, 'Pengembalian ini sudah diproses.');

            $returnedAt = $pengembalianSewa->tanggal_kembali ?? now();
            $lateDays = max(0, $rental->tanggal_selesai->startOfDay()->diffInDays($returnedAt->copy()->startOfDay(), false));
            $dailyFine = (float) (KonfigurasiSistem::where('kunci', 'biaya_keterlambatan_harian')->value('nilai') ?? 0);
            $fine = isset($data['denda']) ? (float) $data['denda'] : $dailyFine * $lateDays;
            $pengembalianSewa->update([
                'diproses_oleh' => $request->user()->id,
                'tanggal_kembali' => $returnedAt,
                'kondisi' => $data['kondisi'],
                'hari_terlambat' => $lateDays,
                'denda' => $fine,
                'status' => 'selesai',
                'catatan' => $data['catatan'] ?? $pengembalianSewa->catatan,
            ]);

            foreach ($rental->items as $item) {
                if (! $item->unit) {
                    continue;
                }

                $unitStatus = $data['kondisi'] === 'baik' ? 'tersedia' : 'servis';
                $item->unit->update([
                    'status' => $unitStatus,
                    'kondisi' => $data['kondisi'],
                ]);

                InspeksiUnit::create([
                    'unit_peralatan_id' => $item->unit->id,
                    'transaksi_sewa_id' => $rental->id,
                    'inspektur_id' => $request->user()->id,
                    'jenis' => 'pengembalian',
                    'kondisi' => $data['kondisi'],
                    'catatan' => $data['catatan'] ?? null,
                    'diperiksa_pada' => now(),
                ]);

                if ($unitStatus === 'servis') {
                    ServisUnit::create([
                        'unit_peralatan_id' => $item->unit->id,
                        'teknisi_id' => $request->user()->id,
                        'judul' => 'Pemeriksaan setelah pengembalian',
                        'deskripsi' => $data['catatan'] ?? 'Unit perlu diperiksa sebelum disewakan kembali.',
                        'status' => 'dijadwalkan',
                        'tanggal_mulai' => now()->toDateString(),
                    ]);
                }
            }

            $rental->update([
                'status' => 'selesai',
                'denda' => $fine,
                'total' => (float) $rental->subtotal + (float) $rental->total_deposit + $fine,
            ]);

            RiwayatStatusSewa::create([
                'transaksi_sewa_id' => $rental->id,
                'user_id' => $request->user()->id,
                'status' => 'selesai',
                'catatan' => 'Pengembalian dan inspeksi unit selesai.',
            ]);
        });

        return back()->with('success', 'Pengembalian diproses dan kondisi unit sudah diperbarui.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unit_peralatan_id' => ['required', 'exists:unit_peralatan,id'],
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'biaya' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        DB::transaction(function () use ($data, $request): void {
            ServisUnit::create([
                ...$data,
                'teknisi_id' => $request->user()->id,
                'status' => 'dijadwalkan',
                'tanggal_mulai' => now()->toDateString(),
            ]);
            UnitPeralatan::whereKey($data['unit_peralatan_id'])
                ->update(['status' => 'servis', 'kondisi' => 'perlu_servis']);
        });

        return back()->with('success', 'Jadwal servis berhasil ditambahkan.');
    }

    public function finishService(ServisUnit $servisUnit): RedirectResponse
    {
        abort_if($servisUnit->status === 'selesai', 422, 'Servis ini sudah selesai.');

        DB::transaction(function () use ($servisUnit): void {
            $servisUnit->update([
                'status' => 'selesai',
                'tanggal_selesai' => now()->toDateString(),
            ]);

            $unit = $servisUnit->unit;
            if (! $unit->servis()->where('status', '!=', 'selesai')->exists()) {
                $unit->update(['status' => 'tersedia', 'kondisi' => 'baik']);
            }
        });

        return back()->with('success', 'Servis ditandai selesai dan unit tersedia kembali.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_usaha' => ['required', 'string', 'max:120'],
            'tagline' => ['required', 'string', 'max:180'],
            'nomor_whatsapp' => ['nullable', 'string', 'max:30'],
            'alamat_usaha' => ['nullable', 'string', 'max:500'],
            'biaya_keterlambatan_harian' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        foreach ($data as $key => $value) {
            KonfigurasiSistem::updateOrCreate(
                ['kunci' => $key],
                [
                    'nilai' => (string) $value,
                    'tipe' => $key === 'biaya_keterlambatan_harian' ? 'number' : 'text',
                    'grup' => in_array($key, ['nomor_whatsapp', 'alamat_usaha'], true) ? 'kontak' : 'umum',
                ],
            );
        }

        return back()->with('success', 'Konfigurasi website berhasil disimpan.');
    }
}
