<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_sewa_id')->constrained('transaksi_sewa')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['transaksi_sewa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_sewa');
    }
};
