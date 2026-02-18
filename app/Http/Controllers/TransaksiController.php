<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\Recipe;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Illuminate\View\View;

class TransaksiController extends Controller
{
    /**
     * Menampilkan Dashboard POS
     */
    public function index(): View
    {
        $menus = Menu::all();
        $stokBahan = BahanBaku::all();
        
        // Ambil 10 transaksi terbaru agar muncul di tabel Riwayat Dashboard
        $riwayat = Transaksi::orderBy('created_at', 'desc')->take(10)->get();
        
        $pendapatan = Transaksi::whereDate('created_at', today())->sum('total_harga');

        return view('dashboard', compact('menus', 'stokBahan', 'pendapatan', 'riwayat'));
    }

    /**
     * Menyimpan Transaksi (Nama fungsi diganti jadi 'checkout' agar sinkron dengan route)
     */
    public function checkout(Request $request)
    {
        // 1. Validasi data
        $request->validate([
            'nama_customer' => 'required',
            'metode_pembayaran' => 'required',
            'total' => 'required|numeric',
            'items' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            try {
                // 2. Gabungkan nama menu jadi teks untuk kolom 'item_list'
                $daftarPesanan = collect($request->items)->map(function($item) {
                    return $item['name'] . " (" . $item['qty'] . "x)";
                })->implode(', ');

                // 3. Simpan Transaksi Utama (Gunakan auth()->id() agar sesuai siapa yang login)
                $transaksi = Transaksi::create([
                    'user_id' => Auth::id(), // <--- BUKAN lagi angka 1, tapi ID yang sedang login
                    'nama_customer' => $request->nama_customer,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'item_list' => $daftarPesanan,
                    'total_harga' => $request->total,
                    'created_at' => now()
                ]);

                // 4. Potong Stok Bahan Baku Berdasarkan Resep
                foreach ($request->items as $item) {
                    $recipes = Recipe::where('menu_id', $item['id'])->get();
                    
                    foreach ($recipes as $recipe) {
                        BahanBaku::where('id', $recipe->bahan_id)
                            ->decrement('stok', $recipe->jumlah_terpakai * $item['qty']);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan!'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        });
    }
}