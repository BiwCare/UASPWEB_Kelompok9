<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeksi_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_peralatan_id')->constrained('unit_peralatan')->cascadeOnDelete();
            $table->foreignId('transaksi_sewa_id')->nullable()->constrained('transaksi_sewa')->nullOnDelete();
            $table->foreignId('inspektur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis', 30)->default('pengembalian');
            $table->string('kondisi', 40);
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamp('diperiksa_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_unit');
    }
};
