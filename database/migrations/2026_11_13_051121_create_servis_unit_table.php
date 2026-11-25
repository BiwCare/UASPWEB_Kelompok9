<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servis_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_peralatan_id')->constrained('unit_peralatan')->cascadeOnDelete();
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->decimal('biaya', 12, 2)->default(0);
            $table->string('status', 30)->default('dijadwalkan')->index();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servis_unit');
    }
};
