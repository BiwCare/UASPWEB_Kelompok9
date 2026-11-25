<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfigurasi_sistem', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 100)->unique();
            $table->text('nilai')->nullable();
            $table->string('tipe', 30)->default('text');
            $table->string('grup', 50)->default('umum')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_sistem');
    }
};
