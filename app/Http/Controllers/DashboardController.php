<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatusSewa;
use App\Models\TransaksiSewa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $rentals = TransaksiSewa::query()
            ->whereBelongsTo($request->user())
            ->with(['items.peralatan', 'items.unit', 'pembayaran', 'pengembalian', 'riwayatStatus'])
            ->latest()
            ->get();

        return view('customer.dashboard', compact('rentals'));
    }

    public function pay(Request $request, TransaksiSewa $transaksiSewa): RedirectResponse
    {
        abort_unless($transaksiSewa->user_id === $request->user()->id, 404);
        abort_if($transaksiSewa->status_pembayaran === 'lunas', 422, 'Pemesanan ini sudah lunas.');

        $data = $request->validate([
            'metode' => ['required', 'in:transfer,tunai'],
            'referensi' => ['nullable', 'string', 'max:100'],
            'bukti_pembayaran' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        abort_if(
            $transaksiSewa->pembayaran()->where('status', 'menunggu_verifikasi')->exists(),
            422,
            'Pembayaran Anda sedang menunggu verifikasi.',
        );

        $proof = $request->file('bukti_pembayaran')?->store('payment-proofs');

        $transaksiSewa->pembayaran()->create([
            'metode' => $data['metode'],
            'jumlah' => (float) $transaksiSewa->total,
            'referensi' => $data['referensi'] ?? null,
            'bukti_pembayaran' => $proof,
            'status' => 'menunggu_verifikasi',
            'catatan' => 'Menunggu verifikasi admin.',
        ]);

        $transaksiSewa->update(['status_pembayaran' => 'menunggu_verifikasi']);

        return back()->with('success', 'Bukti pembayaran berhasil dikirim untuk diperiksa.');
    }

    public function requestReturn(Request $request, TransaksiSewa $transaksiSewa): RedirectResponse
    {
        abort_unless($transaksiSewa->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($transaksiSewa->status === 'sedang_disewa', 422, 'Pengembalian hanya dapat diajukan saat alat sedang disewa.');
        abort_if($transaksiSewa->pengembalian()->exists(), 422, 'Pengembalian untuk pesanan ini sudah diajukan.');

        $transaksiSewa->pengembalian()->create([
            'tanggal_kembali' => now(),
            'status' => 'diajukan',
            'catatan' => $data['catatan'] ?? null,
        ]);

        $transaksiSewa->update(['status' => 'menunggu_pengembalian']);
        RiwayatStatusSewa::create([
            'transaksi_sewa_id' => $transaksiSewa->id,
            'user_id' => $request->user()->id,
            'status' => 'menunggu_pengembalian',
            'catatan' => 'Pelanggan mengajukan pengembalian alat.',
        ]);

        return back()->with('success', 'Pengajuan pengembalian berhasil dikirim.');
    }
}
