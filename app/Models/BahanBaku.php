<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_bakus'; // Pastikan ada 's' di belakangnya
    protected $guarded = [];
    public $timestamps = false;
}
