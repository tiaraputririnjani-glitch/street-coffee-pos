<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Street Coffee POS - Final Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { screens: { 'xs': '475px' } } }
        }
    </script>
    <style>
        .cart-item-enter { animation: slideIn 0.2s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #fed7aa; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #fb923c; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        @media print { .no-print { display: none !important; } .print-area { width: 100% !important; margin: 0 !important; } }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900">

    <div id="shift-modal" class="fixed inset-0 bg-black/95 hidden items-center justify-center z-[5000] p-4 backdrop-blur-2xl no-print">
        <div class="bg-white w-full max-w-sm rounded-[3rem] p-10 text-center shadow-2xl border-4 border-orange-500">
            <div class="text-6xl mb-4">☕</div>
            <h2 class="text-2xl font-black text-gray-800 uppercase mb-2 tracking-tighter">Buka Shift Kasir</h2>
            <p class="text-[10px] text-gray-400 font-bold mb-10 italic uppercase">Wajib Isi Sebelum Berjualan</p>
            <div class="space-y-5 mb-10">
                <div class="text-left">
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-2 mb-1 block">Nama Kasir Bertugas</label>
                    <input type="text" id="cashier-name-input" placeholder="Masukkan Nama..." class="w-full p-5 bg-gray-50 border border-gray-100 rounded-3xl outline-none text-center font-black text-gray-700 focus:ring-4 focus:ring-orange-200 text-lg">
                </div>
                <div class="text-left">
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-2 mb-1 block">Modal Awal Laci (Rp)</label>
                    <input type="number" id="opening-cash" placeholder="0" class="w-full p-5 bg-gray-50 border border-gray-100 rounded-3xl outline-none text-center text-2xl font-black text-orange-600 focus:ring-4 focus:ring-orange-200">
                </div>
            </div>
            <button onclick="startShift()" class="w-full bg-orange-500 text-white py-5 rounded-3xl font-black uppercase tracking-widest shadow-2xl hover:bg-orange-600 active:scale-95 transition-all text-lg">Mulai Bertugas ✨</button>
        </div>
    </div>

    <div id="close-shift-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[4000] p-4 backdrop-blur-md no-print">
        <div class="bg-white w-full max-w-sm rounded-[3rem] p-10 text-center shadow-2xl relative border-4 border-gray-900">
            <button onclick="document.getElementById('close-shift-modal').classList.replace('flex','hidden')" class="absolute top-6 right-8 text-gray-300 hover:text-red-500 font-bold text-3xl cursor-pointer">&times;</button>
            <div class="text-5xl mb-4">🔒</div>
            <h2 class="text-xl font-black text-gray-800 uppercase mb-2">Tutup Kasir</h2>
            <p class="text-xs text-gray-400 font-bold mb-8 italic text-red-500 uppercase font-black">Hitung Uang Fisik Sekarang!</p>
            <div class="mb-8 relative">
                <span class="absolute left-5 top-5 font-black text-orange-300 text-xl">Rp</span>
                <input type="number" id="closing-cash" placeholder="0" class="w-full p-5 pl-14 bg-gray-50 border rounded-3xl outline-none text-center text-2xl font-black text-orange-600 focus:ring-4 focus:ring-orange-100">
            </div>
            <button onclick="endShift()" class="w-full bg-gray-900 text-white py-5 rounded-3xl font-black uppercase shadow-xl hover:bg-black active:scale-95 transition-all">Kirim Laporan & Logout</button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row min-h-screen md:h-screen overflow-hidden bg-gray-100">

        <div class="w-full md:w-20 bg-white shadow-sm md:shadow-lg flex flex-row md:flex-col items-center justify-evenly md:justify-start py-4 md:py-6 space-x-2 md:space-x-0 md:space-y-8 z-30 sticky top-0 md:relative no-print">
            <div class="text-orange-600 font-bold text-2xl hidden md:block">☕</div>
            <button class="filter-btn p-2 md:p-3 bg-orange-100 text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="all">All</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="Coffee">Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="Non-Coffee">Non-Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="Snack">Snack</button>
            
            <div class="md:mt-auto flex flex-row md:flex-col items-center space-x-4 md:space-x-0 md:space-y-4">
                <button onclick="openCloseShiftModal()" class="p-2 md:p-3 text-gray-400 hover:text-red-500 transition-colors text-xl cursor-pointer" title="Tutup Shift Sekarang">🔒</button>
                <button onclick="openInventory()" class="relative p-2 md:p-3 text-gray-400 hover:text-orange-500 transition-colors text-xl cursor-pointer" title="Gudang">
                    📦
                    @php 
                        // FIXED STOCK LOGIC: Hanya hitung yang benar-benar menipis
                        $lowStockItems = $stokBahan->filter(function($item) {
                            return $item->stok <= $item->min_stok;
                        });
                        $lowStockCount = $lowStockItems->count();
                    @endphp
                    @if($lowStockCount > 0)
                        <span class="absolute top-1 right-1 bg-red-500 text-white text-[8px] font-black w-4 h-4 flex items-center justify-center rounded-full animate-bounce">{{ $lowStockCount }}</span>
                    @endif
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf <button type="submit" class="p-2 md:p-3 text-gray-300 hover:text-red-500 transition-colors cursor-pointer">🚪</button></form>
            </div>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto h-full bg-gray-50/50 custom-scroll">
            
            @if($lowStockCount > 0)
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-center justify-between shadow-sm no-print">
                <div class="flex items-center">
                    <span class="mr-3 text-xl">🛒</span>
                    <p class="text-xs font-black text-red-700 uppercase tracking-tighter">Perhatian: Ada {{ $lowStockCount }} bahan segera habis!</p>
                </div>
                <button onclick="openInventory()" class="text-[9px] font-black text-red-500 underline uppercase cursor-pointer">Update Gudang</button>
            </div>
            @endif

            @if(Auth::user()->role == 'admin')
            <div class="flex space-x-6 mb-8 border-b border-gray-200 no-print">
                <button onclick="switchTab('pos')" id="btn-pos" class="pb-3 border-b-4 border-orange-500 font-black text-xs uppercase text-orange-600 tracking-widest cursor-pointer">Kasir POS</button>
                <button onclick="switchTab('rekap')" id="btn-rekap" class="pb-3 text-gray-400 font-black text-xs uppercase hover:text-orange-500 tracking-widest cursor-pointer">Laporan & Audit</button>
            </div>
            @endif

            <div id="section-pos" class="space-y-8 no-print">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 id="main-greeting" class="text-xl md:text-2xl font-black text-gray-800 tracking-tight uppercase">
                            @if(Auth::user()->role == 'admin') HAI OWNER! ☕ @else Halo Kasir! ✨ @endif
                        </h1>
                        <p id="active-cashier-label" class="text-gray-400 text-[10px] font-black uppercase tracking-widest italic">Street Coffee Premium</p>
                    </div>
                    @if(Auth::user()->role == 'admin')
                    <div onclick="switchTab('rekap')" class="bg-orange-500 p-4 md:p-5 rounded-[2rem] shadow-xl text-white text-right cursor-pointer hover:bg-orange-600 transition-all">
                        <span class="text-[9px] uppercase font-black opacity-70 block tracking-widest">Omzet Masuk Hari Ini ⬇️</span>
                        <span class="text-2xl font-black">Rp {{ number_format($pendapatan) }}</span>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    @foreach($menus as $menu)
                    <div class="menu-item bg-white rounded-[2rem] shadow-sm hover:shadow-xl transition-all p-3 md:p-4 group border border-transparent hover:border-orange-100" data-category="{{ $menu->kategori }}">
                        <div class="relative overflow-hidden rounded-2xl mb-4 aspect-square">
                            <img src="{{ $menu->image_url }}" onerror="this.src='https://placehold.co/400x400?text=Coffee'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <h3 class="font-bold text-gray-800 mb-1 text-sm truncate uppercase tracking-tighter">{{ $menu->nama_menu }}</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-orange-600 font-black text-sm">Rp {{ number_format($menu->harga) }}</span>
                            <button class="add-to-cart bg-gray-900 text-white w-8 h-8 rounded-xl hover:bg-orange-600 active:scale-90 transition-all shadow-md flex items-center justify-center font-bold"
                                data-id="{{ $menu->id }}" data-name="{{ $menu->nama_menu }}" data-price="{{ $menu->harga }}">+</button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="daily-report-section" class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-black text-gray-800 mb-6 uppercase tracking-tighter decoration-orange-500 underline italic">📋 Laporan Riwayat Transaksi Harian</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[600px]">
                            <thead><tr class="text-gray-400 text-[10px] uppercase font-black border-b border-gray-50"><th class="pb-4 px-2">Waktu</th><th class="pb-4 px-2">Pelanggan</th><th class="pb-4 px-2">Detail Item</th><th class="pb-4 px-2 text-right">Total</th><th class="pb-4 text-center">Aksi</th></tr></thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-600">
                                @foreach($riwayat as $trx)
                                <tr class="group hover:bg-orange-50 transition-colors">
                                    <td class="py-5 text-gray-400 px-2 font-bold">{{ $trx->created_at->format('H:i') }}</td>
                                    <td class="py-5 font-black uppercase px-2 text-gray-800 text-xs">{{ $trx->nama_customer }}</td>
                                    <td class="py-5 text-gray-500 italic px-2 leading-relaxed text-[10px]">{{ $trx->item_list }}</td>
                                    <td class="py-5 text-right font-black text-orange-600 px-2">Rp {{ number_format($trx->total_harga) }}</td>
                                    <td class="py-5 text-center">
                                        @if(Auth::user()->role == 'admin')
                                        <button onclick="voidTransaction({{ $trx->id }})" class="text-gray-200 hover:text-red-500 transition-colors text-lg cursor-pointer">🗑️</button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="section-rekap" class="hidden space-y-6 print-area">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm text-center"><p class="text-[10px] font-black text-gray-400 uppercase mb-2">Saldo Tunai</p><p id="rekap-cash-display" class="text-2xl font-black text-green-600 tracking-tighter">Rp 0</p></div>
                    <div class="bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm text-center"><p class="text-[10px] font-black text-gray-400 uppercase mb-2">Saldo Digital</p><p id="rekap-digital-display" class="text-2xl font-black text-blue-600 tracking-tighter">Rp 0</p></div>
                    <div onclick="document.getElementById('daily-report-section').scrollIntoView({behavior:'smooth'})" class="bg-orange-500 p-8 rounded-[3rem] shadow-lg text-white text-center cursor-pointer hover:bg-orange-600 transition-all">
                        <p class="text-[10px] font-black uppercase mb-2 opacity-70">Total Keseluruhan ⬇️</p>
                        <p id="rekap-total-display" class="text-3xl font-black tracking-tighter">Rp 0</p>
                    </div>
                </div>
                <div id="audit-result-area" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-black uppercase text-gray-800 underline italic">Hasil Audit Shift Terakhir</h3>
                        <button onclick="localStorage.removeItem('shift_active'); window.location.reload();" class="text-[9px] font-black text-red-400 uppercase border border-red-50 px-3 py-1 rounded-lg no-print cursor-pointer">⚠️ Reset Sesi</button>
                    </div>
                    <div id="audit-content"><p class="text-center text-xs text-gray-400 font-black py-10 uppercase italic">Klik Ikon Gembok (🔒) Untuk Menutup Shift.</p></div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-96 bg-white p-6 shadow-2xl flex flex-col border-l border-gray-100 h-full overflow-y-auto z-20 no-scrollbar no-print">
            <h2 class="text-xl font-black text-gray-800 mb-6 uppercase tracking-tighter">🛒 Keranjang</h2>
            <div class="space-y-4 mb-4"><input type="text" id="customer-name" placeholder="Nama Pelanggan..." class="w-full p-4 bg-gray-50 border rounded-xl outline-none text-sm font-bold"></div>
            <div id="cart-container" class="space-y-3 mb-6 flex-1"><p class="py-10 text-center font-black opacity-20 uppercase text-[10px] italic">Pilih Menu...</p></div>
            <div class="border-t border-gray-50 pt-4 space-y-4">
                <div class="flex justify-between items-center"><span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Bayar</span><span id="cart-total" class="text-2xl font-black text-orange-600 tracking-tighter">Rp 0</span></div>
                <div class="flex flex-col space-y-3">
                    <select id="payment-method" class="w-full p-3 bg-gray-50 border rounded-xl outline-none text-sm font-bold text-gray-700 shadow-sm cursor-pointer">
                        <option value="Cash">💵 Cash / Tunai</option><option value="Dana">💙 Dana</option><option value="Gopay">💚 Gopay</option><option value="Kartu">💳 Kartu Debit/Kredit</option>
                    </select>
                    <div id="cash-calculator" class="p-4 bg-orange-50 rounded-2xl border border-orange-100">
                        <div class="mb-3 text-center"><label class="text-[10px] font-black text-orange-400 uppercase block mb-1">Uang Diterima</label><input type="number" id="cash-amount" placeholder="0" class="w-full p-2 bg-white border rounded-lg outline-none text-sm font-bold text-gray-800 text-center"></div>
                        <div class="flex justify-between items-center"><span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">Kembalian</span><span id="change-amount" class="text-sm font-black text-orange-600">Rp 0</span></div>
                    </div>
                    <button id="btn-checkout" onclick="performCheckout()" class="w-full bg-orange-500 text-white py-4 rounded-2xl font-black text-sm shadow-lg hover:bg-orange-600 active:scale-95 disabled:opacity-30 cursor-pointer" disabled>BAYAR SEKARANG</button>
                </div>
            </div>
        </div>
    </div>

    <div id="inventory-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[8000] p-4 backdrop-blur-sm no-print">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-cart-item-enter">
            <div class="bg-orange-500 p-6 text-white flex justify-between items-center">
                <h2 class="text-xl font-black uppercase tracking-tight">📦 Stok Gudang</h2>
                <button onclick="document.getElementById('inventory-modal').classList.replace('flex','hidden')" class="text-4xl font-bold hover:text-black cursor-pointer">&times;</button>
            </div>
            <div class="p-6">
                <div class="relative mb-6">
                    <input type="text" id="search-inventory" placeholder="Cari bahan kopi..." class="w-full p-4 pl-12 bg-gray-50 border rounded-2xl outline-none text-sm font-bold focus:ring-2 focus:ring-orange-500 transition-all shadow-sm">
                    <span class="absolute left-4 top-4.5 opacity-30 text-xl">🔍</span>
                </div>
                <div id="inventory-list" class="space-y-3 max-h-[350px] overflow-y-auto pr-2 custom-scroll no-scrollbar">
                    @foreach($stokBahan as $bahan)
                    <div class="inventory-item p-4 bg-gray-50 rounded-2xl transition-all border border-transparent hover:border-orange-100" data-name="{{ strtolower($bahan->nama_bahan) }}">
                        <div class="flex justify-between items-center mb-3"><span class="text-xs font-black uppercase text-gray-700 tracking-tighter">{{ $bahan->nama_bahan }}</span><span class="text-xs font-black {{ $bahan->stok <= $bahan->min_stok ? 'text-red-500 animate-pulse' : 'text-green-600' }}">{{ number_format($bahan->stok) }} {{ $bahan->satuan }}</span></div>
                        @if(Auth::user()->role == 'admin')
                        <div class="flex space-x-2"><input type="number" id="restock-qty-{{ $bahan->id }}" placeholder="+ stok" class="flex-1 p-2 bg-white border border-gray-200 rounded-lg outline-none text-[10px] font-bold"><button onclick="restockItem({{ $bahan->id }})" class="bg-gray-900 text-white px-3 py-2 rounded-lg text-[9px] font-black uppercase hover:bg-orange-600 transition-all cursor-pointer">Simpan</button></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="p-6 pt-0"><button onclick="document.getElementById('inventory-modal').classList.replace('flex','hidden')" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black uppercase text-xs hover:bg-black transition-all cursor-pointer">Selesai Cek</button></div>
        </div>
    </div>

    <div id="receipt-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9000] p-4 backdrop-blur-sm">
        <div id="modal-content" class="bg-white w-full max-w-sm rounded-[3rem] shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300 text-center text-xs font-mono">
            <div class="bg-orange-500 p-8 text-white"><div class="text-4xl mb-2">☕</div><h2 class="text-2xl font-black uppercase tracking-widest">Street Coffee</h2></div>
            <div class="p-8 space-y-4 text-left">
                <div class="flex justify-between border-b border-dashed pb-2"><span>Customer:</span><span id="receipt-customer" class="font-black uppercase"></span></div>
                <div id="receipt-cash-details" class="hidden"><div class="flex justify-between border-b border-dashed pb-2 text-gray-400"><span>Bayar:</span><span id="receipt-pay"></span></div><div class="flex justify-between border-b border-dashed pb-2 text-gray-400"><span>Kembali:</span><span id="receipt-change"></span></div></div>
                <div id="receipt-items" class="py-2 space-y-2 max-h-40 overflow-y-auto"></div>
                <div class="border-t-4 pt-6 flex justify-between text-lg font-black"><span>TOTAL</span><span id="receipt-total" class="text-orange-600"></span></div>
            </div>
            <div class="p-8 pt-0"><button onclick="window.location.reload()" class="w-full bg-gray-900 text-white py-5 rounded-3xl font-black hover:bg-black transition-all uppercase tracking-widest shadow-xl cursor-pointer">Tutup Nota</button></div>
        </div>
    </div>

    <script>
        let cart = [];
        const isOwner = {{ Auth::user()->role == 'admin' ? 'true' : 'false' }};

        // 1. FORCED SHIFT SYSTEM & GREETING
        window.addEventListener('DOMContentLoaded', () => {
            const shiftActive = localStorage.getItem('shift_active');
            if (isOwner) {
                updateUIWithActiveShift();
            } else if (!shiftActive) {
                document.getElementById('shift-modal').classList.replace('hidden', 'flex');
            } else {
                updateUIWithActiveShift();
            }
        });

        function updateUIWithActiveShift() {
            const name = localStorage.getItem('current_cashier');
            const greeting = document.getElementById('main-greeting');
            if(isOwner) {
                greeting.innerHTML = "HAI OWNER! ☕"; 
                document.getElementById('active-cashier-label').innerText = name ? "Shift Kasir: " + name : "Belum Ada Kasir Bertugas";
            } else if(name) {
                greeting.innerHTML = `Halo, ${name}! ✨`;
                document.getElementById('active-cashier-label').innerText = "Shift Berjalan: " + name;
            }
        }

        function startShift() {
            const name = document.getElementById('cashier-name-input').value, amount = document.getElementById('opening-cash').value;
            if(!name || !amount) return alert('⚠️ Wajib isi Nama Kasir & Modal Laci!');
            localStorage.setItem('shift_active', 'true'); localStorage.setItem('current_cashier', name);
            localStorage.setItem('opening_cash', amount); localStorage.setItem('total_cash_sales', '0');
            document.getElementById('shift-modal').classList.replace('flex', 'hidden');
            updateUIWithActiveShift();
        }

        function performCheckout() {
            const n = document.getElementById('customer-name').value, m = paymentMethod.value, t = parseInt(document.getElementById('cart-total').innerText.replace(/[^0-9]/g,''));
            if(!n) return alert('⚠️ Mohon isi Nama Pelanggan!'); 
            fetch("{{ route('checkout') }}", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: JSON.stringify({ nama_customer: n, metode_pembayaran: m, total: t, items: cart }) })
            .then(r => r.json()).then(data => {
                if(data.success) {
                    if(m === 'Cash') { const curr = parseFloat(localStorage.getItem('total_cash_sales')) || 0; localStorage.setItem('total_cash_sales', curr + t); }
                    document.getElementById('receipt-customer').innerText = n;
                    document.getElementById('receipt-total').innerText = "Rp " + t.toLocaleString();
                    if(m === 'Cash') {
                        document.getElementById('receipt-cash-details').classList.remove('hidden');
                        document.getElementById('receipt-pay').innerText = "Rp " + parseInt(cashInput.value).toLocaleString();
                        document.getElementById('receipt-change').innerText = document.getElementById('change-amount').innerText;
                    }
                    let itemsHtml = ''; cart.forEach(i => itemsHtml += `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>Rp ${(i.price*i.qty).toLocaleString()}</span></div>`);
                    document.getElementById('receipt-items').innerHTML = itemsHtml;
                    document.getElementById('receipt-modal').classList.replace('hidden', 'flex');
                    setTimeout(() => document.getElementById('modal-content').classList.replace('opacity-0', 'opacity-100'), 50);
                }
            });
        }

        // 3. UTILS
        function openInventory() { document.getElementById('inventory-modal').classList.replace('hidden','flex'); }
        function endShift() {
            const name = localStorage.getItem('current_cashier'), opening = parseFloat(localStorage.getItem('opening_cash')) || 0, sales = parseFloat(localStorage.getItem('total_cash_sales')) || 0, closing = parseFloat(document.getElementById('closing-cash').value) || 0, expected = opening + sales, diff = closing - expected;
            alert('Laporan Audit Shift ' + name + ' Berhasil Dikirim!');
            localStorage.clear(); 
            document.getElementById('logout-form').submit(); // AUTO LOGOUT AFTER SHIFT CLOSE
        }

        function switchTab(t) {
            const pos = document.getElementById('section-pos'), rekap = document.getElementById('section-rekap'), bP = document.getElementById('btn-pos'), bR = document.getElementById('btn-rekap');
            if(t==='pos'){ pos.classList.remove('hidden'); rekap.classList.add('hidden'); bP.classList.add('border-b-4','border-orange-500','text-orange-600'); bR.classList.remove('border-b-4','border-orange-500','text-orange-600'); }
            else { pos.classList.add('hidden'); rekap.classList.remove('hidden'); bR.classList.add('border-b-4','border-orange-500','text-orange-600'); bP.classList.remove('border-b-4','border-orange-500','text-orange-600'); fetchTodaySummary(); }
        }

        function fetchTodaySummary() {
            const today = new Date().toISOString().split('T')[0];
            fetch(`/get-report?start_date=${today}&end_date=${today}`).then(r => r.json()).then(data => {
                document.getElementById('rekap-total-display').innerText = "Rp " + data.total_omzet.toLocaleString();
                document.getElementById('rekap-cash-display').innerText = "Rp " + data.saldo_cash.toLocaleString();
                document.getElementById('rekap-digital-display').innerText = "Rp " + data.saldo_digital.toLocaleString();
            });
        }

        const paymentMethod = document.getElementById('payment-method'), cashInput = document.getElementById('cash-amount'), btnPay = document.getElementById('btn-checkout');
        paymentMethod.addEventListener('change', function() { document.getElementById('cash-calculator').style.display = (this.value === 'Cash') ? 'block' : 'none'; updateCartUI(); });
        cashInput.addEventListener('input', function() {
            const total = parseInt(document.getElementById('cart-total').innerText.replace(/[^0-9]/g,'')) || 0, bayar = parseInt(this.value) || 0, kembalian = bayar - total;
            document.getElementById('change-amount').innerText = "Rp " + (kembalian >= 0 ? kembalian.toLocaleString() : "0");
            btnPay.disabled = (kembalian < 0);
        });

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id'), name = btn.getAttribute('data-name'), price = parseInt(btn.getAttribute('data-price')), exists = cart.find(i => i.id === id);
                if(exists) exists.qty++; else cart.push({id, name, price, qty: 1}); updateCartUI();
            });
        });

        function updateCartUI() {
            const container = document.getElementById('cart-container'), totalDisplay = document.getElementById('cart-total');
            if(cart.length === 0) { container.innerHTML = `<p class="py-10 text-center font-black opacity-20 uppercase text-[10px]">Keranjang Kosong</p>`; totalDisplay.innerText = "Rp 0"; btnPay.disabled = true; return; }
            let html = ''; let total = 0;
            cart.forEach((item, index) => { total += (item.price * item.qty); html += `<div class="flex justify-between items-center bg-gray-50 p-3 rounded-2xl border mb-2 cart-item-enter"><div class="flex-1 pr-2"><p class="font-bold text-gray-800 text-[11px] truncate uppercase tracking-tighter">${item.name}</p><p class="text-[9px] text-gray-400 font-bold">${item.qty}x @ Rp ${item.price.toLocaleString()}</p></div><div class="flex items-center space-x-3"><span class="text-xs font-black text-orange-600">Rp ${(item.price * item.qty).toLocaleString()}</span><button onclick="cart.splice(${index},1);updateCartUI();" class="text-gray-300 hover:text-red-500 font-bold text-lg cursor-pointer">&times;</button></div></div>`; });
            container.innerHTML = html; totalDisplay.innerText = "Rp " + total.toLocaleString(); 
            btnPay.disabled = (paymentMethod.value === 'Cash' && (parseInt(cashInput.value) || 0) < total);
        }

        document.getElementById('search-inventory').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.inventory-item').forEach(item => { item.style.display = item.getAttribute('data-name').includes(q) ? 'flex' : 'none'; });
        });

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => { const target = btn.getAttribute('data-target'); document.querySelectorAll('.filter-btn').forEach(b => b.classList.replace('bg-orange-100','text-gray-400')); btn.classList.add('bg-orange-100', 'text-orange-600'); document.querySelectorAll('.menu-item').forEach(item => { item.style.display = (target === 'all' || item.dataset.category === target) ? 'block' : 'none'; }); });
        });

        function voidTransaction(id) { if(confirm('Hapus transaksi?')) fetch(`/transaksi/${id}`, { method: "DELETE", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } }).then(() => window.location.reload()); }
        function restockItem(id) { const q = document.getElementById('restock-qty-'+id).value; fetch("{{ route('restock') }}", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: JSON.stringify({ bahan_id: id, jumlah: q }) }).then(() => window.location.reload()); }
        function openCloseShiftModal() { document.getElementById('close-shift-modal').classList.replace('hidden', 'flex'); }
    </script>
</body>
</html>