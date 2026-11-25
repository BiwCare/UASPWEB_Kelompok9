<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_peralatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peralatan_id')->constrained('peralatan')->cascadeOnDelete();
            $table->string('kode_unit', 50)->unique();
            $table->string('status', 30)->default('tersedia')->index();
            $table->string('kondisi', 30)->default('baik');
            $table->text('catatan')->nullable();
            $table->date('tanggal_pengadaan')->nullable();
            $table->timestamps();

            $table->index(['peralatan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_peralatan');
    }
};
