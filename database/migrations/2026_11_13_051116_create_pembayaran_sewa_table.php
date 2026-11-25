<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_sewa_id')->constrained('transaksi_sewa')->cascadeOnDelete();
            $table->string('metode', 40);
            $table->decimal('jumlah', 12, 2);
            $table->string('referensi', 100)->nullable()->unique();
            $table->string('bukti_pembayaran')->nullable();
            $table->string('status', 30)->default('menunggu_verifikasi')->index();
            $table->timestamp('dibayar_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_sewa');
    }
};
