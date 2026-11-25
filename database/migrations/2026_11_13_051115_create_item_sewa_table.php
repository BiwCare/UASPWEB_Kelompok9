<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_sewa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_sewa_id')->constrained('transaksi_sewa')->cascadeOnDelete();
            $table->foreignId('peralatan_id')->constrained('peralatan')->restrictOnDelete();
            $table->foreignId('unit_peralatan_id')->nullable()->constrained('unit_peralatan')->nullOnDelete();
            $table->unsignedSmallInteger('jumlah')->default(1);
            $table->decimal('harga_harian', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['peralatan_id', 'unit_peralatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_sewa');
    }
};
