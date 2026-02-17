<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat User Test (Opsional)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. Panggil Seeder Utama kamu (Sangat Penting!)
        // Baris ini yang akan memasukkan 18 menu dan bahan baku kamu
        $this->call([
            StreetCoffeeSeeder::class,
        ]);
    }
}