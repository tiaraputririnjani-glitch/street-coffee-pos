<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Street Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { screens: { 'xs': '475px' } } }
        }
    </script>
    <style>
        .cart-item-enter { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        #receipt-modal { transition: opacity 0.3s ease-out; }
        #modal-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Custom scrollbar agar lebih rapi di desktop */
        @media (min-width: 768px) {
            ::-webkit-scrollbar { width: 5px; height: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex flex-col md:flex-row min-h-screen md:h-screen md:overflow-hidden">

        <div class="w-full md:w-20 bg-white shadow-sm md:shadow-lg flex flex-row md:flex-col items-center justify-evenly md:justify-start py-4 md:py-6 space-x-2 md:space-x-0 md:space-y-8 z-10 sticky top-0 md:relative overflow-x-auto no-scrollbar px-4">
            <div class="text-orange-600 font-bold text-2xl hidden md:block">☕</div>
            <button class="filter-btn p-2 md:p-3 bg-orange-100 text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="all">All</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Coffee">Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Non-Coffee">Non-Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Snack">Snack</button>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto md:h-full">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-8 space-y-4 md:space-y-0">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Street Coffee Menu</h1>
                    <p class="text-gray-500 text-xs md:text-sm">Pilih menu favorit pelanggan kamu</p>
                </div>
                <div class="bg-orange-500 text-white px-4 py-2 md:px-6 md:py-3 rounded-xl md:rounded-2xl shadow-lg text-right self-end md:self-auto">
                    <span class="text-[10px] md:text-xs block opacity-80 uppercase tracking-wider">Pendapatan Hari Ini</span>
                    <span class="text-lg md:text-xl font-bold">Rp {{ number_format($pendapatan) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6 mb-8 md:mb-12">
                @foreach($menus as $menu)
                <div class="menu-item bg-white rounded-2xl md:rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-3 md:p-4 group" data-category="{{ $menu->kategori }}">
                    <div class="relative overflow-hidden rounded-xl md:rounded-2xl mb-3 md:mb-4">
                        <img src="{{ $menu->image_url }}" 
                             onerror="this.src='https://placehold.co/400x300?text=Kopi+Enak'" 
                             class="w-full h-32 md:h-40 object-cover group-hover:scale-110 transition-transform duration-500" 
                             alt="{{ $menu->nama_menu }}">
                    </div>
                    <h3 class="font-bold text-gray-800 mb-1 text-sm md:text-base truncate">{{ $menu->nama_menu }}</h3>
                    <div class="flex justify-between items-center">
                        <span class="text-orange-600 font-extrabold text-sm md:text-lg">Rp {{ number_format($menu->harga) }}</span>
                        <button class="add-to-cart bg-orange-500 text-white w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl hover:bg-orange-600 active:scale-95 transition-all shadow-md flex items-center justify-center font-bold text-lg md:text-xl"
                            data-id="{{ $menu->id }}" data-name="{{ $menu->nama_menu }}" data-price="{{ $menu->harga }}">+</button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="bg-white rounded-3xl p-4 md:p-8 shadow-sm border border-gray-100 hidden xs:block">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg md:text-xl font-extrabold text-gray-800 tracking-tight">📋 Riwayat Pesanan Hari Ini</h2>
                    <span class="text-[10px] font-black text-orange-500 bg-orange-50 px-3 py-1 rounded-full uppercase tracking-widest">Live History</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-50">
                                <th class="pb-4 font-black px-2">Waktu</th>
                                <th class="pb-4 font-black px-2">Customer</th>
                                <th class="pb-4 font-black px-2 hidden md:table-cell">Pesanan</th>
                                <th class="pb-4 font-black text-center px-2">Metode</th>
                                <th class="pb-4 font-black text-right px-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($riwayat as $trx)
                            <tr class="text-sm hover:bg-gray-50 transition-colors group">
                                <td class="py-4 text-gray-400 px-2">{{ $trx->created_at->format('H:i') }}</td>
                                <td class="py-4 font-bold text-gray-800 uppercase px-2">{{ $trx->nama_customer }}</td>
                                <td class="py-4 text-gray-600 italic text-xs max-w-xs truncate leading-relaxed px-2 hidden md:table-cell">
                                    {{ $trx->item_list }} </td>
                                <td class="py-4 text-center px-2">
                                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-500 text-[10px] font-black uppercase italic whitespace-nowrap">{{ $trx->metode_pembayaran }}</span>
                                </td>
                                <td class="py-4 text-right font-black text-orange-600 px-2 whitespace-nowrap">Rp {{ number_format($trx->total_harga) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($riwayat->isEmpty())
                        <div class="flex flex-col items-center py-8 md:py-12 text-gray-300">
                            <span class="text-4xl mb-2">📒</span>
                            <p class="italic text-sm">Belum ada transaksi hari ini...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="w-full md:w-96 bg-white p-4 md:p-6 shadow-[0_-5px_15px_-5px_rgba(0,0,0,0.1)] md:shadow-2xl flex flex-col border-t md:border-t-0 md:border-l border-gray-100 md:h-full z-20">
            <h2 class="text-lg md:text-xl font-extrabold text-gray-800 mb-4 md:mb-6 flex items-center"><span class="mr-2">🛒</span> Current Order</h2>
            <div class="mb-3 md:mb-4">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Customer Name</label>
                <input type="text" id="customer-name" placeholder="Nama Pelanggan..." class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl md:rounded-2xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all text-sm">
            </div>
            <div class="mb-4 md:mb-6">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Payment Method</label>
                <select id="payment-method" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl md:rounded-2xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all appearance-none text-sm">
                    <option value="Cash">💵 Cash</option>
                    <option value="Dana">💙 Dana</option>
                    <option value="Gopay">💚 Gopay</option>
                    <option value="Kartu">💳 Kartu Debit/Kredit</option>
                </select>
            </div>

            <div id="cart-container" class="flex-1 overflow-y-auto space-y-3 mb-4 pr-2 min-h-[150px] md:min-h-0">
                <div class="flex flex-col items-center justify-center h-full text-gray-300 italic text-sm"><p>Keranjang masih kosong...</p></div>
            </div>

            <div class="border-t border-gray-100 pt-4 md:pt-6 space-y-4">
                <div class="flex justify-between items-center font-black text-xl text-gray-800">
                    <span class="text-xs md:text-sm text-gray-400 uppercase tracking-tighter">Total Harga</span>
                    <span id="cart-total" class="text-orange-600 text-lg md:text-xl">Rp 0</span>
                </div>
                <button id="btn-checkout" class="w-full bg-orange-500 text-white py-3 md:py-4 rounded-2xl md:rounded-3xl font-black text-lg hover:bg-orange-600 shadow-lg shadow-orange-100 transition-all active:scale-95 disabled:bg-gray-100 disabled:text-gray-300 disabled:shadow-none disabled:cursor-not-allowed" disabled>Bayar Sekarang</button>
            </div>

            <div class="mt-4 md:mt-8 pt-4 md:pt-6 border-t border-gray-100 overflow-y-auto max-h-40 md:max-h-56 pr-1 hidden md:block">
                <h3 class="font-black text-gray-800 mb-3 md:mb-4 flex items-center text-xs md:text-sm uppercase tracking-widest">📦 Inventory Status</h3>
                <div class="grid grid-cols-2 md:grid-cols-1 gap-2">
                    @foreach($stokBahan as $bahan)
                    <div class="flex justify-between items-center p-2 md:p-3 bg-gray-50 rounded-xl md:rounded-2xl group hover:bg-white hover:shadow-sm transition-all">
                        <span class="font-bold text-gray-600 text-[10px] md:text-xs truncate mr-2">{{ $bahan->nama_bahan }}</span>
                        <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter whitespace-nowrap
                            {{ $bahan->stok <= $bahan->min_stok ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                            {{ number_format($bahan->stok) }} {{ $bahan->satuan }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="receipt-modal" class="fixed inset-0 bg-black/60 hidden flex items-end md:items-center justify-center z-50 p-4 backdrop-blur-md">
        <div id="modal-content" class="bg-white w-full max-w-sm rounded-t-[30px] md:rounded-[40px] shadow-2xl overflow-hidden transform scale-95 opacity-0 duration-300 mb-0 md:mb-auto">
            <div class="bg-orange-500 p-6 md:p-8 text-white text-center">
                <div class="text-3xl md:text-4xl mb-2 animate-bounce">☕</div>
                <h2 class="text-xl md:text-2xl font-black uppercase tracking-tighter">Street Coffee</h2>
                <p class="text-[10px] opacity-70 tracking-widest">NOTA TRANSAKSI DIGITAL</p>
            </div>
            <div class="p-6 md:p-8 space-y-4 md:space-y-5 text-sm text-gray-600 font-mono">
                <div class="flex justify-between border-b border-dashed border-gray-200 pb-2"><span>Customer:</span><span id="receipt-customer" class="font-black text-gray-800 uppercase text-right break-words ml-4"></span></div>
                <div class="flex justify-between border-b border-dashed border-gray-200 pb-2"><span>Metode:</span><span id="receipt-method" class="font-black text-gray-800"></span></div>
                <div id="receipt-items" class="py-2 space-y-2 max-h-40 overflow-y-auto pr-2"></div>
                <div class="border-t-2 border-gray-100 pt-4 flex justify-between text-lg md:text-xl font-black text-gray-800"><span>TOTAL</span><span id="receipt-total" class="text-orange-600"></span></div>
            </div>
            <div class="p-6 md:p-8 pt-0"><button onclick="closeReceipt()" class="w-full bg-gray-900 text-white py-4 md:py-5 rounded-2xl md:rounded-3xl font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-xl text-sm md:text-base">Selesai</button></div>
        </div>
    </div>

    <script>
        // =============================================
        // KODINGAN JAVASCRIPT (TIDAK ADA PERUBAHAN)
        // =============================================
        let cart = [];

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const price = parseInt(button.getAttribute('data-price'));
                const existingItem = cart.find(item => item.id === id);
                if (existingItem) { existingItem.qty++; } else { cart.push({ id, name, price, qty: 1 }); }
                updateCartUI();
                // Efek getar di HP saat tambah item (opsional)
                if (navigator.vibrate) navigator.vibrate(50);
            });
        });

        function updateCartUI() {
            const container = document.getElementById('cart-container');
            const totalDisplay = document.getElementById('cart-total');
            const btnCheckout = document.getElementById('btn-checkout');
            if (cart.length === 0) {
                container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-gray-300 italic text-sm"><p>Keranjang masih kosong...</p></div>`;
                totalDisplay.innerText = "Rp 0"; btnCheckout.disabled = true; return;
            }
            let html = ''; let total = 0;
            cart.forEach((item, index) => {
                total += item.price * item.qty;
                html += `<div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100 cart-item-enter">
                    <div class="flex-1 overflow-hidden"><p class="font-bold text-gray-800 text-sm truncate">${item.name}</p><p class="text-[10px] text-gray-400">${item.qty} pcs x Rp ${item.price.toLocaleString()}</p></div>
                    <div class="flex items-center space-x-2 ml-2"><span class="font-black text-orange-600 text-sm whitespace-nowrap">Rp ${(item.price * item.qty).toLocaleString()}</span><button onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 font-bold p-1">&times;</button></div>
                </div>`;
            });
            container.innerHTML = html; totalDisplay.innerText = `Rp ${total.toLocaleString()}`; btnCheckout.disabled = false;
            // Scroll otomatis ke bawah keranjang saat item ditambah
            container.scrollTop = container.scrollHeight;
        }

        function removeFromCart(index) { cart.splice(index, 1); updateCartUI(); }

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                document.querySelectorAll('.filter-btn').forEach(b => { b.classList.remove('bg-orange-100', 'text-orange-600'); b.classList.add('text-gray-400'); });
                btn.classList.add('bg-orange-100', 'text-orange-600'); btn.classList.remove('text-gray-400');
                document.querySelectorAll('.menu-item').forEach(item => { const category = item.getAttribute('data-category'); item.style.display = (target === 'all' || category === target) ? 'block' : 'none'; });
            });
        });

        document.getElementById('btn-checkout').addEventListener('click', function() {
            const customerName = document.getElementById('customer-name').value;
            const paymentMethod = document.getElementById('payment-method').value;
            const totalHargaText = document.getElementById('cart-total').innerText;
            const totalHarga = parseInt(totalHargaText.replace(/[^0-9]/g, ''));
            if (!customerName) {
                document.getElementById('customer-name').focus();
                return alert('⚠️ Masukkan nama pelanggan dulu ya!');
            }
            
            const btn = this; btn.disabled = true; btn.innerHTML = '<span class="animate-pulse">Memproses...</span>';

            fetch("{{ route('checkout') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ nama_customer: customerName, metode_pembayaran: paymentMethod, total: totalHarga, items: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('receipt-customer').innerText = customerName;
                    document.getElementById('receipt-method').innerText = paymentMethod;
                    document.getElementById('receipt-total').innerText = totalHargaText;
                    let itemsHtml = ''; cart.forEach(item => { itemsHtml += `<div class="flex justify-between text-xs"><span>${item.name} (x${item.qty})</span><span>Rp ${(item.price * item.qty).toLocaleString()}</span></div>`; });
                    document.getElementById('receipt-items').innerHTML = itemsHtml;
                    
                    const modal = document.getElementById('receipt-modal'); const content = document.getElementById('modal-content');
                    modal.classList.remove('hidden'); 
                    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
                } else { 
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan di server.')); 
                    btn.disabled = false; btn.innerText = 'Bayar Sekarang'; 
                }
            })
            .catch(error => {
                alert('Error koneksi! Pastikan internet lancar.');
                btn.disabled = false; btn.innerText = 'Bayar Sekarang';
            });
        });

        function closeReceipt() { window.location.reload(); }
    </script>
</body>
</html>