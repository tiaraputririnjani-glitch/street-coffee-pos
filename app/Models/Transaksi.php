<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model {
    protected $table = 'transaksis'; // Pastikan nama tabel sesuai
    protected $guarded = []; // WAJIB: Agar data total_harga bisa disimpan
}