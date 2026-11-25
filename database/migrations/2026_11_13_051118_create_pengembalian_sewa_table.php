<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengembalian_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_sewa_id')->unique()->constrained('transaksi_sewa')->cascadeOnDelete();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_kembali')->nullable();
            $table->string('kondisi', 40)->default('menunggu_pemeriksaan');
            $table->unsignedSmallInteger('hari_terlambat')->default(0);
            $table->decimal('denda', 12, 2)->default(0);
            $table->string('status', 30)->default('diajukan')->index();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian_sewa');
    }
};
