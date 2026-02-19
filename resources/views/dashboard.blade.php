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
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #fed7aa; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #fb923c; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <div id="shift-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] p-4 backdrop-blur-md">
        <div class="bg-white w-full max-w-sm rounded-[3rem] p-10 text-center shadow-2xl">
            <div class="text-4xl mb-4">☀️</div>
            <h2 class="text-xl font-black text-gray-800 uppercase mb-2">Buka Kasir</h2>
            <p class="text-xs text-gray-400 font-bold mb-8 italic">Masukkan uang modal di laci hari ini</p>
            <div class="mb-8 relative">
                <span class="absolute left-4 top-4 font-black text-orange-300">Rp</span>
                <input type="number" id="opening-cash" placeholder="0" class="w-full p-4 pl-12 bg-gray-50 border border-gray-100 rounded-2xl outline-none text-center text-xl font-black text-orange-600 focus:ring-4 focus:ring-orange-100">
            </div>
            <button onclick="startShift()" class="w-full bg-orange-500 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl hover:bg-orange-600 active:scale-95 transition-all">Mulai Jualan</button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row min-h-screen md:h-screen overflow-hidden bg-gray-100">

        <div class="w-full md:w-20 bg-white shadow-sm md:shadow-lg flex flex-row md:flex-col items-center justify-evenly md:justify-start py-4 md:py-6 space-x-2 md:space-x-0 md:space-y-8 z-30 sticky top-0 md:relative">
            <div class="text-orange-600 font-bold text-2xl hidden md:block">☕</div>
            <button class="filter-btn p-2 md:p-3 bg-orange-100 text-orange-600 rounded-xl text-sm flex-shrink-0 font-bold" data-target="all">All</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Coffee">Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Non-Coffee">Non-Coffee</button>
            <button class="filter-btn p-2 md:p-3 text-gray-400 hover:text-orange-600 rounded-xl text-sm flex-shrink-0" data-target="Snack">Snack</button>
            
            <div class="md:mt-auto flex flex-row md:flex-col items-center space-x-4 md:space-x-0 md:space-y-4">
                <button id="open-inventory" class="p-2 md:p-3 text-gray-400 hover:text-orange-500 transition-colors text-xl" title="Cek Stok Gudang">📦</button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 md:p-3 text-gray-300 hover:text-red-500 transition-colors"><span class="text-xl">🚪</span></button>
                </form>
            </div>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto h-full bg-gray-50/50 custom-scroll">
            @if(Auth::user()->role == 'admin')
            <div class="flex space-x-6 mb-8 border-b border-gray-200">
                <button onclick="switchTab('pos')" id="btn-pos" class="pb-3 border-b-4 border-orange-500 font-black text-xs uppercase tracking-widest text-orange-600">Kasir POS</button>
                <button onclick="switchTab('rekap')" id="btn-rekap" class="pb-3 text-gray-400 font-black text-xs uppercase tracking-widest hover:text-orange-500">Rekap Mingguan</button>
            </div>
            @endif

            <div id="section-pos" class="space-y-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-gray-800 tracking-tight uppercase">Halo, {{ Auth::user()->name }}!</h1>
                        <p class="text-gray-400 text-xs font-bold">Pilih menu untuk pesanan baru</p>
                    </div>
                    @if(Auth::user()->role == 'admin')
                    <div class="bg-orange-500 p-3 md:p-4 rounded-2xl shadow-lg text-white text-right">
                        <span class="text-[9px] uppercase font-black opacity-70 block tracking-wider">Pendapatan Hari Ini</span>
                        <span class="text-lg font-black">Rp {{ number_format($pendapatan) }}</span>
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
            </div>

            <div id="section-rekap" class="hidden space-y-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                    <h2 class="text-xl font-black text-gray-800 mb-6 uppercase tracking-tighter">Filter Laporan Penjualan</h2>
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Dari Tanggal</label>
                            <input type="date" id="date-start" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none text-sm font-bold">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Hingga Tanggal</label>
                            <input type="date" id="date-end" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none text-sm font-bold">
                        </div>
                        <button onclick="filterReport()" class="bg-gray-900 text-white px-8 py-3.5 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-600 transition-all shadow-lg">Lihat Laporan</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">Total Omzet Periode Ini</p>
                        <p id="rekap-total-display" class="text-3xl font-black text-orange-600 tracking-tighter">Rp 0</p>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm text-center flex flex-col items-center justify-center">
                        <button onclick="localStorage.removeItem('shift_active'); window.location.reload();" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-red-500 hover:text-white transition-all">⚠️ Reset Shift Kasir</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-96 bg-white p-6 shadow-2xl flex flex-col border-l border-gray-100 h-full overflow-y-auto z-20 no-scrollbar">
            <h2 class="text-xl font-black text-gray-800 mb-6 flex items-center tracking-tighter uppercase">🛒 Pesanan</h2>
            <div class="space-y-4 mb-4">
                <input type="text" id="customer-name" placeholder="Nama Pelanggan..." class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none text-sm font-bold">
            </div>

            <div id="cart-container" class="space-y-3 mb-6">
                <div class="flex flex-col items-center justify-center text-gray-300 italic text-sm py-10"><p>Pilih menu...</p></div>
            </div>

            <div class="border-t border-gray-50 pt-4 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Bayar</span>
                    <span id="cart-total" class="text-2xl font-black text-orange-600 tracking-tighter">Rp 0</span>
                </div>
                <div class="flex flex-col space-y-3">
                    <select id="payment-method" class="w-full p-3 bg-gray-50 border border-gray-100 rounded-xl outline-none text-sm font-bold text-gray-700 shadow-sm">
                        <option value="Cash">💵 Cash / Tunai</option>
                        <option value="Dana">💙 Dana</option>
                        <option value="Gopay">💚 Gopay</option>
                        <option value="Kartu">💳 Kartu Debit/Kredit</option>
                    </select>

                    <div id="cash-calculator" class="p-4 bg-orange-50 rounded-2xl border border-orange-100">
                        <div class="mb-3 text-center">
                            <label class="text-[10px] font-black text-orange-400 uppercase block mb-1">Uang Diterima</label>
                            <input type="number" id="cash-amount" placeholder="0" class="w-full p-2 bg-white border border-orange-200 rounded-lg outline-none text-sm font-bold text-gray-800 text-center">
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-gray-400 uppercase">Kembalian</span>
                            <span id="change-amount" class="text-sm font-black text-orange-600">Rp 0</span>
                        </div>
                    </div>

                    <button id="btn-checkout" class="w-full bg-orange-500 text-white py-4 rounded-2xl font-black text-sm shadow-lg hover:bg-orange-600 active:scale-95 transition-all disabled:opacity-30 disabled:bg-gray-200" disabled>BAYAR SEKARANG</button>
                </div>
            </div>
        </div>
    </div>

    <div id="inventory-modal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[110] p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-cart-item-enter">
            <div class="bg-orange-500 p-6 text-white flex justify-between items-center">
                <h2 class="text-xl font-black uppercase tracking-tight">📦 Stok Gudang</h2>
                <button id="close-inventory" class="text-3xl font-bold hover:text-black">&times;</button>
            </div>
            <div class="p-6">
                <div class="relative mb-6">
                    <input type="text" id="search-inventory" placeholder="Cari bahan..." class="w-full p-3 pl-10 bg-gray-50 border border-gray-100 rounded-xl outline-none text-sm font-bold focus:ring-2 focus:ring-orange-500">
                    <span class="absolute left-3 top-3.5 opacity-30 text-sm">🔍</span>
                </div>
                <div id="inventory-list" class="space-y-2 max-h-[350px] overflow-y-auto pr-2 custom-scroll no-scrollbar">
                    @foreach($stokBahan as $bahan)
                    <div class="inventory-item flex justify-between items-center p-3 bg-gray-50 rounded-xl transition-all hover:bg-orange-50" data-name="{{ strtolower($bahan->nama_bahan) }}">
                        <span class="text-xs font-black text-gray-600 uppercase">{{ $bahan->nama_bahan }}</span>
                        <span class="text-xs font-black {{ $bahan->stok <= $bahan->min_stok ? 'text-red-500 animate-pulse' : 'text-green-600' }}">{{ number_format($bahan->stok) }} {{ $bahan->satuan }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="p-6 pt-0"><button id="btn-close-inv" class="w-full bg-gray-900 text-white py-3 rounded-xl font-black uppercase text-xs">Selesai Cek</button></div>
        </div>
    </div>

    <div id="receipt-modal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[120] p-4 backdrop-blur-sm">
        <div id="modal-content" class="bg-white w-full max-w-sm rounded-[3rem] shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
            <div class="bg-orange-500 p-8 text-white text-center">
                <div class="text-4xl mb-2">☕</div>
                <h2 class="text-2xl font-black uppercase tracking-tighter">Street Coffee</h2>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-widest">Transaksi Berhasil</p>
            </div>
            <div class="p-8 space-y-4 text-xs font-mono text-gray-600">
                <div class="flex justify-between border-b border-dashed pb-2"><span>Customer:</span><span id="receipt-customer" class="font-black text-gray-900 uppercase"></span></div>
                <div class="flex justify-between border-b border-dashed pb-2"><span>Metode:</span><span id="receipt-method" class="font-black text-gray-900 uppercase"></span></div>
                <div id="receipt-cash-details" class="hidden">
                    <div class="flex justify-between border-b border-dashed pb-2 text-gray-400"><span>Bayar:</span><span id="receipt-pay" class="font-bold"></span></div>
                    <div class="flex justify-between border-b border-dashed pb-2 text-gray-400"><span>Kembalian:</span><span id="receipt-change" class="font-bold text-orange-600"></span></div>
                </div>
                <div id="receipt-items" class="py-2 space-y-2 max-h-40 overflow-y-auto pr-1"></div>
                <div class="border-t-4 pt-6 flex justify-between text-lg font-black text-gray-900 uppercase"><span>TOTAL</span><span id="receipt-total" class="text-orange-600"></span></div>
            </div>
            <div class="p-8 pt-0"><button onclick="window.location.reload()" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black hover:bg-black transition-all uppercase tracking-widest shadow-xl">Selesai</button></div>
        </div>
    </div>

    <script>
        let cart = [];

        // 1. INGATAN SHIFT (Cek saat halaman dibuka)
        window.addEventListener('DOMContentLoaded', () => {
            if (!localStorage.getItem('shift_active')) {
                document.getElementById('shift-modal').classList.replace('hidden', 'flex');
            }
        });

        function startShift() {
            const amount = document.getElementById('opening-cash').value;
            if(!amount || amount < 0) return alert('Input uang modal dulu ya!');
            localStorage.setItem('shift_active', 'true');
            document.getElementById('shift-modal').classList.replace('flex', 'hidden');
        }

        // 2. TAB SWITCHING (ADMIN ONLY)
        function switchTab(target) {
            const pos = document.getElementById('section-pos');
            const rekap = document.getElementById('section-rekap');
            const btnPos = document.getElementById('btn-pos');
            const btnRekap = document.getElementById('btn-rekap');

            if(target === 'pos') {
                pos.classList.remove('hidden'); rekap.classList.add('hidden');
                btnPos.classList.add('border-b-4', 'border-orange-500', 'text-orange-600');
                btnRekap.classList.remove('border-b-4', 'border-orange-500', 'text-orange-600');
            } else {
                pos.classList.add('hidden'); rekap.classList.remove('hidden');
                btnRekap.classList.add('border-b-4', 'border-orange-500', 'text-orange-600');
                btnPos.classList.remove('border-b-4', 'border-orange-500', 'text-orange-600');
            }
        }

        // 3. FITUR LAPORAN (REPORT)
        function filterReport() {
            const start = document.getElementById('date-start').value;
            const end = document.getElementById('date-end').value;

            if(!start || !end) return alert('Pilih rentang tanggal dulu, Tiara!');

            fetch(`/get-report?start_date=${start}&end_date=${end}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('rekap-total-display').innerText = "Rp " + data.total_omzet.toLocaleString();
                    if(data.total_order === 0) alert('Belum ada penjualan di tanggal ini.');
                })
                .catch(() => alert('Gangguan koneksi!'));
        }

        // 4. GUDANG MODAL & SEARCH
        const invModal = document.getElementById('inventory-modal');
        document.getElementById('open-inventory').addEventListener('click', () => invModal.classList.remove('hidden'));
        document.getElementById('close-inventory').addEventListener('click', () => invModal.classList.add('hidden'));
        document.getElementById('btn-close-inv').addEventListener('click', () => invModal.classList.add('hidden'));

        document.getElementById('search-inventory').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.inventory-item').forEach(item => {
                item.style.display = item.getAttribute('data-name').includes(query) ? 'flex' : 'none';
            });
        });

        // 5. KERANJANG & CHECKOUT
        const paymentMethod = document.getElementById('payment-method');
        const cashInput = document.getElementById('cash-amount');
        const changeDisplay = document.getElementById('change-amount');
        const btnPay = document.getElementById('btn-checkout');

        paymentMethod.addEventListener('change', function() {
            document.getElementById('cash-calculator').style.display = (this.value === 'Cash') ? 'block' : 'none';
            updateCartUI();
        });

        cashInput.addEventListener('input', function() {
            const total = parseInt(document.getElementById('cart-total').innerText.replace(/[^0-9]/g,'')) || 0;
            const bayar = parseInt(this.value) || 0;
            const kembalian = bayar - total;
            changeDisplay.innerText = "Rp " + (kembalian >= 0 ? kembalian.toLocaleString() : "0");
            btnPay.disabled = (this.value === '' || (this.value !== '' && kembalian < 0));
        });

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

            if(cart.length === 0) {
                container.innerHTML = `<div class="flex flex-col items-center justify-center text-gray-300 italic py-10"><p>Pesanan kosong...</p></div>`;
                totalDisplay.innerText = "Rp 0"; btnPay.disabled = true; return;
            }

            let html = ''; let total = 0;
            cart.forEach((item, index) => {
                total += (item.price * item.qty);
                html += `<div class="flex justify-between items-center bg-gray-50 p-3 rounded-2xl border cart-item-enter">
                    <div class="flex-1 pr-2">
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
            btnPay.disabled = (paymentMethod.value === 'Cash' && (parseInt(cashInput.value) || 0) < total);
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
            const method = paymentMethod.value;
            const totalVal = parseInt(document.getElementById('cart-total').innerText.replace(/[^0-9]/g,''));
            if(!name) return alert('⚠️ Tulis nama pelanggan!');
            this.disabled = true; this.innerText = 'WAIT...';

            fetch("{{ route('checkout') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ nama_customer: name, metode_pembayaran: method, total: totalVal, items: cart })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('receipt-customer').innerText = name;
                    document.getElementById('receipt-method').innerText = method;
                    document.getElementById('receipt-total').innerText = "Rp " + totalVal.toLocaleString();
                    if(method === 'Cash') {
                        document.getElementById('receipt-cash-details').classList.remove('hidden');
                        document.getElementById('receipt-pay').innerText = "Rp " + parseInt(cashInput.value).toLocaleString();
                        document.getElementById('receipt-change').innerText = changeDisplay.innerText;
                    }
                    let itemsHtml = '';
                    cart.forEach(i => itemsHtml += `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span class="font-bold">Rp ${(i.price*i.qty).toLocaleString()}</span></div>`);
                    document.getElementById('receipt-items').innerHTML = itemsHtml;
                    document.getElementById('receipt-modal').classList.remove('hidden');
                    setTimeout(() => document.getElementById('modal-content').classList.replace('opacity-0','opacity-100'), 50);
                } else alert('Gagal: ' + data.message);
            });
        });
    </script>
</body>
</html>