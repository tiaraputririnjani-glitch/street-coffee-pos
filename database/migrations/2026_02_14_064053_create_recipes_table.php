<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('recipes', function (Blueprint $table) {
        $table->id();
        // Menghubungkan ke tabel menus
        $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade'); 
        // Menghubungkan ke tabel bahan_bakus (pastikan namanya bahan_bakus pakai 's')
        $table->foreignId('bahan_id')->constrained('bahan_bakus')->onDelete('cascade'); 
        $table->integer('jumlah_terpakai'); // Contoh: 15 gram per porsi
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
