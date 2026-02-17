<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
    Schema::create('transaksis', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->string('nama_customer');
    $table->string('metode_pembayaran');
    $table->text('item_list'); // <--- WAJIB ADA untuk menampung daftar kopi & snack
    $table->integer('total_harga');
    $table->timestamps();
});
}

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};