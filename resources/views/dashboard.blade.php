<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Street Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .cart-item-enter { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        #receipt-modal { transition: opacity 0.3s ease-out; }
        #modal-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        /* Custom scrollbar agar lebih rapi */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-100 font-sans overflow-hidden">
    <div class="flex h-screen">
        <div class="w-20 bg-white shadow-lg flex flex-col items-center py-6 space-y-8">
            <div class="text-orange-600 font-bold text-2xl">☕</div>
            <button class="filter-btn p-3 bg-orange-100 text-orange-600 rounded-xl" data-target="all">All</button>
            <button class="filter-btn p-3 text-gray-400 hover:text-orange-600" data-target="Coffee">Coffee</button>
            <button class="filter-btn p-3 text-gray-400 hover:text-orange-600" data-target="Non-Coffee">Non-Coffee</button>
            <button class="filter-btn p-3 text-gray-400 hover:text-orange-600" data-target="Snack">Snack</button>
        </div>

        <div class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Street Coffee Menu</h1>
                    <p class="text-gray-500 text-sm">Pilih menu favorit pelanggan kamu</p>
                </div>
                <div class="bg-orange-500 text-white px-6 py-3 rounded-2xl shadow-lg text-right">
                    <span class="text-xs block opacity-80 uppercase tracking-wider">Pendapatan Hari Ini</span>
                    <span class="text-xl font-bold">Rp {{ number_format($pendapatan) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
                @foreach($menus as $menu)
                <div class="menu-item bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-4 group" data-category="{{ $menu->kategori }}">
                    <div class="relative overflow-hidden rounded-2xl mb-4">
                        <img src="{{ $menu->image_url }}" 
                             onerror="this.src='https://placehold.co/400x300?text=Kopi+Enak'" 
                             class="w-full h-40 object-cover group-hover:scale-110 transition-transform duration-500" 
                             alt="{{ $menu->nama_menu }}">
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1 text-sm md:text-base">{{ $menu->nama_menu }}</h3>
                    <div class="flex justify-between items-center">
                        <span class="text-orange-600 font-extrabold text-lg">Rp {{ number_format($menu->harga) }}</span>
                        <button class="add-to-cart bg-orange-500 text-white w-10 h-10 rounded-xl hover:bg-orange-600 active:scale-95 transition-all shadow-md flex items-center justify-center font-bold text-xl"
                            data-id="{{ $menu->id }}" data-name="{{ $menu->nama_menu }}" data-price="{{ $menu->harga }}">+</button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-extrabold text-gray-800 tracking-tight">📋 Riwayat Pesanan Hari Ini</h2>
                    <span class="text-[10px] font-black text-orange-500 bg-orange-50 px-3 py-1 rounded-full uppercase tracking-widest">Live History</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-50">
                                <th class="pb-4 font-black">Waktu</th>
                                <th class="pb-4 font-black">Customer</th>
                                <th class="pb-4 font-black">Pesanan (Menu)</th>
                                <th class="pb-4 font-black text-center">Metode</th>
                                <th class="pb-4 font-black text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($riwayat as $trx)
                            <tr class="text-sm hover:bg-gray-50 transition-colors group">
                                <td class="py-4 text-gray-400">{{ $trx->created_at->format('H:i') }}</td>
                                <td class="py-4 font-bold text-gray-800 uppercase">{{ $trx->nama_customer }}</td>
                                <td class="py-4 text-gray-600 italic text-xs max-w-xs truncate leading-relaxed">
                                    {{ $trx->item_list }} </td>
                                <td class="py-4 text-center">
                                    <span class="px-3 py-1 rounded-lg bg-gray-100 text-gray-500 text-[10px] font-black uppercase italic">{{ $trx->metode_pembayaran }}</span>
                                </td>
                                <td class="py-4 text-right font-black text-orange-600">Rp {{ number_format($trx->total_harga) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($riwayat->isEmpty())
                        <div class="flex flex-col items-center py-12 text-gray-300">
                            <span class="text-4xl mb-2">📒</span>
                            <p class="italic text-sm">Belum ada transaksi hari ini...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="w-96 bg-white p-6 shadow-2xl flex flex-col h-full border-l border-gray-100">
            <h2 class="text-xl font-extrabold text-gray-800 mb-6 flex items-center"><span class="mr-2">🛒</span> Current Order</h2>
            <div class="mb-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Customer Name</label>
                <input type="text" id="customer-name" placeholder="Nama Pelanggan..." class="w-full p-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all">
            </div>
            <div class="mb-6">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Payment Method</label>
                <select id="payment-method" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all appearance-none">
                    <option value="Cash">💵 Cash</option>
                    <option value="Dana">💙 Dana</option>
                    <option value="Gopay">💚 Gopay</option>
                    <option value="Kartu">💳 Kartu Debit/Kredit</option>
                </select>
            </div>

            <div id="cart-container" class="flex-1 overflow-y-auto space-y-4 mb-6 pr-2">
                <div class="flex flex-col items-center justify-center h-full text-gray-300 italic"><p>Keranjang masih kosong...</p></div>
            </div>

            <div class="border-t border-gray-100 pt-6 space-y-4">
                <div class="flex justify-between items-center font-black text-xl text-gray-800">
                    <span class="text-sm text-gray-400 uppercase tracking-tighter">Total Harga</span>
                    <span id="cart-total" class="text-orange-600">Rp 0</span>
                </div>
                <button id="btn-checkout" class="w-full bg-orange-500 text-white py-4 rounded-3xl font-black text-lg hover:bg-orange-600 shadow-lg shadow-orange-100 transition-all active:scale-95 disabled:bg-gray-100 disabled:text-gray-300 disabled:shadow-none disabled:cursor-not-allowed" disabled>Bayar Sekarang</button>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 overflow-y-auto max-h-56 pr-1">
                <h3 class="font-black text-gray-800 mb-4 flex items-center text-sm uppercase tracking-widest">📦 Inventory Status</h3>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($stokBahan as $bahan)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-2xl group hover:bg-white hover:shadow-sm transition-all">
                        <span class="font-bold text-gray-600 text-xs">{{ $bahan->nama_bahan }}</span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter
                            {{ $bahan->stok <= $bahan->min_stok ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                            {{ number_format($bahan->stok) }} {{ $bahan->satuan }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="receipt-modal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-md">
        <div id="modal-content" class="bg-white w-full max-w-sm rounded-[40px] shadow-2xl overflow-hidden transform scale-95 opacity-0 duration-300">
            <div class="bg-orange-500 p-8 text-white text-center">
                <div class="text-4xl mb-2 animate-bounce">☕</div>
                <h2 class="text-2xl font-black uppercase tracking-tighter">Street Coffee</h2>
                <p class="text-[10px] opacity-70 tracking-widest">NOTA TRANSAKSI DIGITAL</p>
            </div>
            <div class="p-8 space-y-5 text-sm text-gray-600 font-mono">
                <div class="flex justify-between border-b border-dashed border-gray-200 pb-2"><span>Customer:</span><span id="receipt-customer" class="font-black text-gray-800 uppercase"></span></div>
                <div class="flex justify-between border-b border-dashed border-gray-200 pb-2"><span>Metode:</span><span id="receipt-method" class="font-black text-gray-800"></span></div>
                <div id="receipt-items" class="py-2 space-y-2"></div>
                <div class="border-t-2 border-gray-100 pt-4 flex justify-between text-xl font-black text-gray-800"><span>TOTAL</span><span id="receipt-total" class="text-orange-600"></span></div>
            </div>
            <div class="p-8 pt-0"><button onclick="closeReceipt()" class="w-full bg-gray-900 text-white py-5 rounded-3xl font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-xl">Selesai</button></div>
        </div>
    </div>

    <script>
        let cart = [];

        // 1. Logika Keranjang
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const price = parseInt(button.getAttribute('data-price'));
                const existingItem = cart.find(item => item.id === id);
                if (existingItem) { existingItem.qty++; } else { cart.push({ id, name, price, qty: 1 }); }
                updateCartUI();
            });
        });

        function updateCartUI() {
            const container = document.getElementById('cart-container');
            const totalDisplay = document.getElementById('cart-total');
            const btnCheckout = document.getElementById('btn-checkout');
            if (cart.length === 0) {
                container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-gray-300 italic"><p>Keranjang masih kosong...</p></div>`;
                totalDisplay.innerText = "Rp 0"; btnCheckout.disabled = true; return;
            }
            let html = ''; let total = 0;
            cart.forEach((item, index) => {
                total += item.price * item.qty;
                html += `<div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100 cart-item-enter">
                    <div class="flex-1"><p class="font-bold text-gray-800 text-sm">${item.name}</p><p class="text-[10px] text-gray-400">${item.qty} pcs x Rp ${item.price.toLocaleString()}</p></div>
                    <div class="flex items-center space-x-3"><span class="font-black text-orange-600 text-sm">Rp ${(item.price * item.qty).toLocaleString()}</span><button onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 font-bold">&times;</button></div>
                </div>`;
            });
            container.innerHTML = html; totalDisplay.innerText = `Rp ${total.toLocaleString()}`; btnCheckout.disabled = false;
        }

        function removeFromCart(index) { cart.splice(index, 1); updateCartUI(); }

        // 2. Filter Kategori
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                document.querySelectorAll('.filter-btn').forEach(b => { b.classList.remove('bg-orange-100', 'text-orange-600'); b.classList.add('text-gray-400'); });
                btn.classList.add('bg-orange-100', 'text-orange-600'); btn.classList.remove('text-gray-400');
                document.querySelectorAll('.menu-item').forEach(item => { const category = item.getAttribute('data-category'); item.style.display = (target === 'all' || category === target) ? 'block' : 'none'; });
            });
        });

        // 3. Proses Checkout & Modal
        document.getElementById('btn-checkout').addEventListener('click', function() {
            const customerName = document.getElementById('customer-name').value;
            const paymentMethod = document.getElementById('payment-method').value;
            const totalHargaText = document.getElementById('cart-total').innerText;
            const totalHarga = parseInt(totalHargaText.replace(/[^0-9]/g, ''));
            if (!customerName) return alert('⚠️ Masukkan nama pelanggan!');
            
            const btn = this; btn.disabled = true; btn.innerText = 'Tunggu...';

            fetch("{{ route('checkout') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ nama_customer: customerName, metode_pembayaran: paymentMethod, total: totalHarga, items: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Isi data nota
                    document.getElementById('receipt-customer').innerText = customerName;
                    document.getElementById('receipt-method').innerText = paymentMethod;
                    document.getElementById('receipt-total').innerText = totalHargaText;
                    let itemsHtml = ''; cart.forEach(item => { itemsHtml += `<div class="flex justify-between"><span>${item.name} (x${item.qty})</span><span>Rp ${(item.price * item.qty).toLocaleString()}</span></div>`; });
                    document.getElementById('receipt-items').innerHTML = itemsHtml;
                    
                    // Munculkan Modal
                    const modal = document.getElementById('receipt-modal'); const content = document.getElementById('modal-content');
                    modal.classList.remove('hidden'); 
                    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
                } else { alert('Gagal: ' + data.message); btn.disabled = false; btn.innerText = 'Bayar Sekarang'; }
            });
        });

        function closeReceipt() { window.location.reload(); }
    </script>
</body>
</html>