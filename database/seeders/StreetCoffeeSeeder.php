<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{BahanBaku, Menu, Recipe, User};

class StreetCoffeeSeeder extends Seeder {
    public function run(): void {
        User::updateOrCreate(
            ['email' => 'tiaraputririnjani@gmail.com'],
            ['name' => 'Tiara', 'password' => bcrypt('password')]
        );

        // 1. Tambahkan Saus & Topping di sini
        $bahanList = [
            'Biji Kopi'     => ['stok' => 10000, 'satuan' => 'gram', 'min_stok' => 500],
            'Susu'          => ['stok' => 20000, 'satuan' => 'ml',   'min_stok' => 1000],
            'Bubuk Teh'     => ['stok' => 5000,  'satuan' => 'gram', 'min_stok' => 200],
            'Bubuk Matcha'  => ['stok' => 2000,  'satuan' => 'gram', 'min_stok' => 100],
            'Bubuk Cokelat' => ['stok' => 3000,  'satuan' => 'gram', 'min_stok' => 100],
            'Saus Sambal'   => ['stok' => 1000,  'satuan' => 'gram', 'min_stok' => 100], // Baru
            'Mayones'       => ['stok' => 1000,  'satuan' => 'gram', 'min_stok' => 100], // Baru
            'Pisang'        => ['stok' => 100,   'satuan' => 'pcs',  'min_stok' => 10],
            'Sosis'         => ['stok' => 100,   'satuan' => 'pcs',  'min_stok' => 10],
            'Roti'          => ['stok' => 50,    'satuan' => 'pcs',  'min_stok' => 5],
            'Cokelat Meses' => ['stok' => 2000,  'satuan' => 'gram', 'min_stok' => 100], // Baru
            'Keju'          => ['stok' => 2000,  'satuan' => 'gram', 'min_stok' => 100], // Baru
            'Dimsum'        => ['stok' => 100,   'satuan' => 'pcs',  'min_stok' => 10],
            'Cup'           => ['stok' => 1000,  'satuan' => 'pcs',  'min_stok' => 50],
        ];

        $createdBahan = [];
        foreach ($bahanList as $nama => $data) {
            $createdBahan[$nama] = BahanBaku::updateOrCreate(['nama_bahan' => $nama], $data);
        }

        // 2. Daftar Menu (Tetap 18 Menu)
        $list = [
            ['Espresso', 10000, 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=400', 'Coffee'],
            ['Americano', 12000, 'https://mataseni.net/wp-content/uploads/2024/12/Mengenal-Caffe-Americano-Minuman-Kopi-Simpel-dengan-Rasa-yang-Kuat-1024x768.jpg', 'Coffee'],
            ['Kopi Susu Aren', 15000, 'https://images.unsplash.com/photo-1559496417-e7f25cb247f3?w=400', 'Coffee'],
            ['Iced Latte', 18000, 'https://thehealthfulideas.com/wp-content/uploads/2022/03/Iced-Dirty-Chai-Latte-14.jpg', 'Coffee'],
            ['Cappuccino', 18000, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400', 'Coffee'],
            ['Iced Mocha', 19000, 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?w=400', 'Coffee'],
            ['Caramel Macchiato', 19000, 'https://images.unsplash.com/photo-1599398054066-846f28917f38?w=400', 'Coffee'],
            ['Thai Tea', 10000, 'https://www.wokandskillet.com/wp-content/uploads/2015/07/Thai-Iced-Tea.jpg', 'Non-Coffee'],
            ['Lemon Tea', 10000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400', 'Non-Coffee'],
            ['Taro Latte', 15000, 'https://www.matchamexico.mx/wp-content/uploads/2023/04/sweet-taro-coco-5.jpg', 'Non-Coffee'],
            ['Red Velvet', 15000, 'https://starbuckspr.com/wp-content/uploads/2023/11/STBX_HolidayProducts2023_v01_RedVelvet_IcedLatte-1024x1024.png', 'Non-Coffee'],
            ['Matcha Latte', 18000, 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?w=400', 'Non-Coffee'],
            ['Chocolate', 18000, 'https://coffeeland.co.id/wp-content/uploads/2023/01/mon-jester-VqQDk11L3UU-unsplash-533x800.jpg', 'Non-Coffee'],
            ['Pisang Goreng', 10000, 'https://fibercreme.com/wp-content/uploads/2025/06/Header-3.webp', 'Snack'],
            ['Sosis Bakar', 12000, 'https://cdn.idntimes.com/content-images/community/2020/12/fromandroid-2f48ec126fbe5311c2612b835bb85c4a.jpg', 'Snack'],
            ['Toast Bread', 15000, 'https://www.iloveborneo.my/wp-content/uploads/2022/10/292675123_5216043448486969_8573207897945905160_n.jpg', 'Snack'],
            ['French Fries', 15000, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400', 'Snack'],
            ['Dimsum Ayam', 18000, 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=400', 'Snack'],
        ];

        foreach ($list as $item) {
            $m = Menu::updateOrCreate(['nama_menu' => $item[0]], ['harga' => $item[1], 'image_url' => $item[2], 'kategori' => $item[3]]);

            // Tambahkan Cup & Resep (Termasuk Topping & Saus)
            if ($item[3] != 'Snack') Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Cup']->id], ['jumlah_terpakai' => 1]);

            if ($item[0] == 'Toast Bread') {
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Roti']->id], ['jumlah_terpakai' => 1]);
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Cokelat Meses']->id], ['jumlah_terpakai' => 10]);
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Keju']->id], ['jumlah_terpakai' => 5]);
            } elseif ($item[0] == 'Sosis Bakar') {
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Sosis']->id], ['jumlah_terpakai' => 2]);
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Saus Sambal']->id], ['jumlah_terpakai' => 10]);
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Mayones']->id], ['jumlah_terpakai' => 5]);
            } elseif ($item[0] == 'Dimsum Ayam') {
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Dimsum']->id], ['jumlah_terpakai' => 4]);
                Recipe::updateOrCreate(['menu_id' => $m->id, 'bahan_id' => $createdBahan['Saus Sambal']->id], ['jumlah_terpakai' => 5]);
            }
        }
    }
}