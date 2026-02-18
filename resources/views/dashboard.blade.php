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
        .cart-item-enter { animation: slideIn 0.2s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @media (min-width: 768px) {
            ::-webkit-scrollbar { width: 4px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #f3f4f6; border-radius: 10px; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <div class="flex flex-col md:flex-row min-h-screen md:h-screen md:overflow-hidden">

        <div class="w-full md:w-20 bg-white shadow-sm md:shadow-lg flex flex-row md:flex-col items-center justify-evenly md:justify-start py-4 md:py-6 space-x-2 md:space-x-0 md:space-y-8 z-10 sticky top-0 md:relative overflow-x-auto no-scrollbar px-4">
            <div class="text-orange-600 font-bold text-2xl hidden md:block">☕</div>
            <button class="filter-btn p-2 md:p-3 bg-orange-100 text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="all">All</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Coffee">Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Non-Coffee">Non-Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Snack">Snack</button>
            
            <form action="{{ route('logout') }}" method="POST" class="md:mt-auto">
                @csrf
                <button type="submit" class="p-2 md:p-3 text-gray-300 hover:text-red-500 transition-colors" title="Logout">
                    <span class="text-xl">🚪</span>
                </button>
            </form>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto md:h-full bg-gray-50/50">
            
            <div class="space-y-2 mb-6">
                @foreach($stokBahan as $bahan)
                    @if($bahan->stok <= $bahan->min_stok)
                        <div class="bg-white border-l-4 border-red-500 p-3 rounded-r-xl shadow-sm flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="mr-3">⚠️</span>
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-tighter">Sisa {{ number_format($bahan->stok) }} {{ $bahan->satuan }} {{ $bahan->nama_bahan }}!</span>
                            </div>
                            <span class="text-[9px] font-black text-red-500 uppercase animate-pulse">Beli Lagi</span>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-gray-800 tracking-tight">Halo, {{ Auth::user()->name }}!</h1>
                    <p class="text-gray-400 text-xs">Ayo buat kopi terenak hari ini</p>
                </div>
                @if(Auth::user()->role == 'admin')
                <div class="bg-orange-500 p-3 md:p-4 rounded-2xl shadow-lg shadow-orange-100 text-white text-right">
                    <span class="text-[9px] uppercase font-black opacity-70 block tracking-wider">Pendapatan Hari Ini</span>
                    <span class="text-lg font-black">Rp {{ number_format($pendapatan) }}</span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mb-10">
                @foreach($menus as $menu)
                <div class="menu-item bg-white rounded-[2rem] shadow-sm hover:shadow-xl transition-all p-3 md:p-4 group border border-transparent hover:border-orange-100" data-category="{{ $menu->kategori }}">
                    <div class="relative overflow-hidden rounded-2xl mb-4 aspect-square">
                        <img src="{{ $menu->image_url }}" onerror="this.src='https://placehold.co/400x400?text=Street+Coffee'" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
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

            @if(Auth::user()->role == 'admin')
            <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100">
                <h2 class="text-lg font-black text-gray-800 mb-6 flex items-center"><span class="mr-2 text-orange-500">📋</span> Riwayat Pesanan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]">
                        <thead>
                            <tr class="text-gray-400 text-[10px] uppercase font-black border-b border-gray-50">
                                <th class="pb-4 px-2">Waktu</th>
                                <th class="pb-4 px-2">Customer</th>
                                <th class="pb-4 px-2">Item</th>
                                <th class="pb-4 px-2">Metode</th> <th class="pb-4 px-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($riwayat as $trx)
                            <tr class="text-xs">
                                <td class="py-4 text-gray-400 px-2">{{ $trx->created_at->format('H:i') }}</td>
                                <td class="py-4 font-bold text-gray-800 px-2 uppercase">{{ $trx->nama_customer }}</td>
                                <td class="py-4 text-gray-500 italic px-2">{{ $trx->item_list }}</td>
                                <td class="py-4 px-2">
                                    <span class="bg-gray-100 px-2 py-1 rounded-lg text-[10px] font-black uppercase italic text-gray-500 whitespace-nowrap">{{ $trx->metode_pembayaran }}</span>
                                </td>
                                <td class="py-4 text-right font-black text-orange-600 px-2">Rp {{ number_format($trx->total_harga) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <div class="w-full md:w-96 bg-white p-6 shadow-2xl flex flex-col border-l border-gray-50 md:h-full z-20">
            <h2 class="text-xl font-black text-gray-800 mb-6 flex items-center"><span class="mr-2">🛒</span> Keranjang</h2>
            
            <div class="space-y-4 mb-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Customer Name</label>
                    <input type="text" id="customer-name" placeholder="Siapa namanya?..." class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none text-sm font-medium">
                </div>
            </div>

            <div id="cart-container" class="flex-1 min-h-[150px] overflow-y-auto mb-4 space-y-3 no-scrollbar pr-1 border-b border-gray-50">
                <div class="flex flex-col items-center justify-center h-full text-gray-300 italic text-sm py-10">
                    <p>Pilih menu di samping...</p>
                </div>
            </div>

            <div class="pt-4 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-tighter">Total Bayar</span>
                    <span id="cart-total" class="text-2xl font-black text-orange-600">Rp 0</span>
                </div>
                <div class="flex space-x-2">
                    <select id="payment-method" class="flex-1 p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none text-sm appearance-none font-bold text-gray-700">
                        <option value="Cash">💵 Cash</option>
                        <option value="Dana">💙 Dana</option>
                        <option value="Gopay">💚 Gopay</option>
                        <option value="Kartu">💳 Kartu</option>
                    </select>
                    <button id="btn-checkout" class="flex-[2] bg-orange-500 text-white py-3 rounded-xl font-black text-sm shadow-lg shadow-orange-100 hover:bg-orange-600 active:scale-95 transition-all disabled:bg-gray-100 disabled:text-gray-300" disabled>BAYAR SEKARANG</button>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-50 overflow-y-auto no-scrollbar max-h-48">
                <h3 class="text-[10px] font-black text-gray-400 uppercase mb-3 tracking-widest">Status Gudang</h3>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($stokBahan as $bahan)
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg hover:bg-orange-50 transition-colors">
                        <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $bahan->nama_bahan }}</span>
                        <span class="text-[10px] font-black {{ $bahan->stok <= $bahan->min_stok ? 'text-red-500 animate-pulse' : 'text-green-600' }}">{{ number_format($bahan->stok) }} {{ $bahan->satuan }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="receipt-modal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div id="modal-content" class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
            <div class="bg-orange-500 p-8 text-white text-center">
                <div class="text-4xl mb-2">☕</div>
                <h2 class="text-2xl font-black uppercase">Street Coffee</h2>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-widest">Digital Receipt</p>
            </div>
            <div class="p-8 space-y-4 text-xs font-mono text-gray-600">
                <div class="flex justify-between border-b border-dashed pb-2"><span>Customer:</span><span id="receipt-customer" class="font-black text-gray-900 text-right uppercase"></span></div>
                <div class="flex justify-between border-b border-dashed pb-2"><span>Metode:</span><span id="receipt-method" class="font-black text-gray-900 uppercase"></span></div>
                <div id="receipt-items" class="py-2 space-y-2 max-h-40 overflow-y-auto pr-1"></div>
                <div class="border-t-2 pt-4 flex justify-between text-lg font-black text-gray-900"><span>TOTAL</span><span id="receipt-total" class="text-orange-600"></span></div>
            </div>
            <div class="p-8 pt-0"><button onclick="window.location.reload()" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black hover:bg-black transition-all uppercase tracking-widest shadow-xl">Selesai</button></div>
        </div>
    </div>

    <script>
        let cart = [];

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const price = parseInt(btn.getAttribute('data-price'));
                const exists = cart.find(i => i.id === id);
                if(exists) exists.qty++; else cart.push({id, name, price, qty: 1});
                updateCartUI();
            });
        });

        function updateCartUI() {
            const container = document.getElementById('cart-container');
            const totalDisplay = document.getElementById('cart-total');
            const btnPay = document.getElementById('btn-checkout');

            if(cart.length === 0) {
                container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-gray-300 italic text-sm py-10"><p>Keranjang Kosong...</p></div>`;
                totalDisplay.innerText = "Rp 0"; btnPay.disabled = true; return;
            }

            let html = ''; let total = 0;
            cart.forEach((item, index) => {
                total += (item.price * item.qty);
                html += `
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded-2xl border border-gray-100 cart-item-enter mb-2">
                    <div class="flex-1 min-w-0 pr-2">
                        <p class="font-bold text-gray-800 text-[11px] truncate uppercase">${item.name}</p>
                        <p class="text-[9px] text-gray-400 font-bold">${item.qty}x @ Rp ${item.price.toLocaleString()}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-black text-orange-600">Rp ${(item.price * item.qty).toLocaleString()}</span>
                        <button onclick="removeItem(${index})" class="text-gray-300 hover:text-red-500 font-bold text-lg">&times;</button>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
            totalDisplay.innerText = "Rp " + total.toLocaleString();
            btnPay.disabled = false;
        }

        function removeItem(i) { cart.splice(i, 1); updateCartUI(); }

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.replace('bg-orange-100','text-gray-400'));
                btn.classList.add('bg-orange-100', 'text-orange-600');
                document.querySelectorAll('.menu-item').forEach(item => {
                    item.style.display = (target === 'all' || item.dataset.category === target) ? 'block' : 'none';
                });
            });
        });

        document.getElementById('btn-checkout').addEventListener('click', function() {
            const name = document.getElementById('customer-name').value;
            const method = document.getElementById('payment-method').value;
            const totalVal = parseInt(document.getElementById('cart-total').innerText.replace(/[^0-9]/g,''));
            
            if(!name) return alert('⚠️ Tulis nama pelanggan dulu ya!');
            
            this.disabled = true; this.innerText = 'PROSES...';

            fetch("{{ route('checkout') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ nama_customer: name, metode_pembayaran: method, total: totalVal, items: cart })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('receipt-customer').innerText = name;
                    document.getElementById('receipt-method').innerText = method; // Update Metode di Nota
                    document.getElementById('receipt-total').innerText = document.getElementById('cart-total').innerText;
                    
                    let itemsHtml = '';
                    cart.forEach(i => itemsHtml += `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>Rp ${(i.price*i.qty).toLocaleString()}</span></div>`);
                    document.getElementById('receipt-items').innerHTML = itemsHtml;
                    
                    document.getElementById('receipt-modal').classList.remove('hidden');
                    setTimeout(() => document.getElementById('modal-content').classList.replace('opacity-0','opacity-100'), 50);
                } else alert('Gagal Simpan: ' + data.message);
            })
            .catch(() => {
                alert('Koneksi internet bermasalah!');
                this.disabled = false; this.innerText = 'BAYAR';
            });
        });
    </script>
</body>
</html>