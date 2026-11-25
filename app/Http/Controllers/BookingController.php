<?php

namespace App\Http\Controllers;

use App\Models\ItemSewa;
use App\Models\Peralatan;
use App\Models\RiwayatStatusSewa;
use App\Models\TransaksiSewa;
use App\Models\UnitPeralatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'peralatan_id' => ['required', 'integer', 'exists:peralatan,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:20'],
            'alamat_pengiriman' => ['required', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $rental = DB::transaction(function () use ($data, $request): TransaksiSewa {
            $equipment = Peralatan::query()
                ->whereKey($data['peralatan_id'])
                ->where('aktif', true)
                ->lockForUpdate()
                ->firstOrFail();

            $activeStatuses = ['menunggu_verifikasi', 'disetujui', 'sedang_disewa'];
            $units = UnitPeralatan::query()
                ->where('peralatan_id', $equipment->id)
                ->where('status', 'tersedia')
                ->whereDoesntHave('itemSewa.transaksiSewa', function ($query) use ($activeStatuses, $data) {
                    $query->whereIn('status', $activeStatuses)
                        ->whereDate('tanggal_mulai', '<=', $data['tanggal_selesai'])
                        ->whereDate('tanggal_selesai', '>=', $data['tanggal_mulai']);
                })
                ->lockForUpdate()
                ->limit((int) $data['jumlah'])
                ->get();

            if ($units->count() !== (int) $data['jumlah']) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah alat yang tersedia tidak cukup pada tanggal tersebut.',
                ]);
            }

            $days = Carbon::parse($data['tanggal_mulai'])->diffInDays(Carbon::parse($data['tanggal_selesai'])) + 1;
            $subtotal = (float) $equipment->harga_per_hari * (int) $data['jumlah'] * $days;
            $deposit = (float) $equipment->deposit * (int) $data['jumlah'];

            $rental = TransaksiSewa::create([
                'user_id' => $request->user()->id,
                'nomor_sewa' => 'SK-'.Str::upper(Str::random(10)),
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jumlah_hari' => $days,
                'subtotal' => $subtotal,
                'total_deposit' => $deposit,
                'total' => $subtotal + $deposit,
                'status' => 'menunggu_verifikasi',
                'status_pembayaran' => 'belum_dibayar',
                'alamat_pengiriman' => $data['alamat_pengiriman'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($units as $unit) {
                ItemSewa::create([
                    'transaksi_sewa_id' => $rental->id,
                    'peralatan_id' => $equipment->id,
                    'unit_peralatan_id' => $unit->id,
                    'jumlah' => 1,
                    'harga_harian' => $equipment->harga_per_hari,
                    'subtotal' => $equipment->harga_per_hari * $days,
                ]);
            }

            RiwayatStatusSewa::create([
                'transaksi_sewa_id' => $rental->id,
                'user_id' => $request->user()->id,
                'status' => $rental->status,
                'catatan' => 'Pemesanan dibuat.',
            ]);

            return $rental;
        });

        return redirect()->route('dashboard')->with('success', "Pemesanan {$rental->nomor_sewa} berhasil dikirim.");
    }
}
