<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
   // Di dalam file ...create_bahan_bakus_table.php
Schema::create('bahan_bakus', function (Blueprint $table) { // Pastikan 'bahan_bakus'
    $table->id();
    $table->string('nama_bahan');
    $table->integer('stok');
    $table->string('satuan');
    $table->integer('min_stok')->default(0);
    $table->timestamps();

});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
