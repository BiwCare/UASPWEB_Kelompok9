<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peralatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_peralatan_id')->constrained('kategori_peralatan')->restrictOnDelete();
            $table->string('nama', 150);
            $table->string('slug')->unique();
            $table->string('merek', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('spesifikasi')->nullable();
            $table->decimal('harga_per_hari', 12, 2);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->string('gambar')->nullable();
            $table->boolean('unggulan')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'unggulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peralatan');
    }
};
