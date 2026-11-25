<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('nomor_sewa', 32)->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedSmallInteger('jumlah_hari');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total_deposit', 12, 2)->default(0);
            $table->decimal('denda', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status', 40)->default('menunggu_verifikasi')->index();
            $table->string('status_pembayaran', 30)->default('belum_dibayar')->index();
            $table->text('alamat_pengiriman')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_sewa');
    }
};
