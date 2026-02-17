<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{protected $guarded = [];
public $timestamps = false; // Agar tidak error 'updated_at' lagi seperti kemarin!
    //
}
