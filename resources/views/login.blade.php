<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Street Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-[40px] shadow-2xl w-full max-w-md border border-gray-50">
        <div class="text-center mb-8">
            <div class="text-5xl mb-4">☕</div>
            <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tighter">Street Coffee</h1>
            <p class="text-gray-400 text-sm">Silakan login untuk akses kasir</p>
        </div>

        <form action="/login" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Kasir</label>
                <input type="email" name="email" class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="email@gmail.com" required>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password" class="w-full p-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="••••••••" required>
            </div>
            
            @if($errors->any())
                <p class="text-red-500 text-xs font-bold">{{ $errors->first() }}</p>
            @endif

            <button type="submit" class="w-full bg-orange-500 text-white py-4 rounded-3xl font-black uppercase tracking-widest hover:bg-orange-600 shadow-lg shadow-orange-100 transition-all active:scale-95">Masuk Sekarang</button>
        </form>
    </div>
</body>
</html>