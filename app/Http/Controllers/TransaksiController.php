<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\Recipe;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        
        // Ambil 10 transaksi terbaru agar muncul di tabel Riwayat
        $riwayat = Transaksi::orderBy('created_at', 'desc')->take(10)->get();
        
        // Hitung omzet hari ini saja untuk dashboard utama
        $pendapatan = Transaksi::whereDate('created_at', today())->sum('total_harga');

        return view('dashboard', compact('menus', 'stokBahan', 'pendapatan', 'riwayat'));
    }

    /**
     * Menyimpan Transaksi
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'nama_customer' => 'required',
            'metode_pembayaran' => 'required',
            'total' => 'required|numeric',
            'items' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $daftarPesanan = collect($request->items)->map(function($item) {
                    return $item['name'] . " (" . $item['qty'] . "x)";
                })->implode(', ');

                $transaksi = Transaksi::create([
                    'user_id' => Auth::id(),
                    'nama_customer' => $request->nama_customer,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'item_list' => $daftarPesanan,
                    'total_harga' => $request->total,
                    'created_at' => now()
                ]);

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

    /**
     * FITUR LAPORAN (Baru & Sudah Diperbaiki)
     */
    public function getReport(Request $request) 
    {
        // Pastikan format tanggal benar
        $start = $request->start_date . " 00:00:00";
        $end = $request->end_date . " 23:59:59";

        // Cari transaksi di antara tanggal yang dipilih
        $transaksi = Transaksi::whereBetween('created_at', [$start, $end])->get();

        return response()->json([
            'total_omzet' => $transaksi->sum('total_harga'),
            'total_order' => $transaksi->count(),
            'data' => $transaksi
        ]);
    }
}