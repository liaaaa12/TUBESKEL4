<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MP Mart - Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 600;
            color: white !important;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        .nav-link:hover {
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
        }
        .product-price {
            font-size: 1.1rem;
            color: #4a5568;
            font-weight: 500;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #667eea;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .quantity-btn:hover {
            background: #764ba2;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 5px;
        }
        .btn-add-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .btn-add-cart:hover {
            opacity: 0.9;
            color: white;
        }
        .cart-badge {
            position: relative;
            top: -8px;
            right: -8px;
            padding: 3px 6px;
            border-radius: 50%;
            background: #e53e3e;
            color: white;
            font-size: 0.7rem;
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .cart-icon {
            position: relative;
            margin-right: 20px;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 20px;
            overflow-y: auto;
        }
        .cart-modal.active {
            display: block;
        }
        .cart-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .cart-overlay.active {
            display: block;
        }
        .marquee-container { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); color: white; font-weight: 500; font-size: 1rem; display: flex; overflow: hidden; }
        .marquee-container marquee { flex: 1; padding: 5px 0; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">MP Mart</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link cart-icon" href="#" onclick="toggleCart()">
                            <i class="bi bi-cart3 fs-4"></i>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('riwayat.transaksi') }}" onclick="window.location.href='{{ route('riwayat.transaksi') }}'">
                            <i class="bi bi-clock-history"></i> Riwayat Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('password.change') }}">
                            <i class="bi bi-key"></i> Ubah Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="nav-link" href="https://wa.me/62895619859193" target="_blank" title="Hubungi Admin">
                            <i class="bi bi-whatsapp fs-4" style="color: #25d366;"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Modal -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
    <div class="cart-modal" id="cartModal">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Keranjang Belanja</h4>
            <button class="btn-close" onclick="toggleCart()"></button>
        </div>
        <div id="cart-items">
            <!-- Cart items will be added here dynamically -->
        </div>
        <hr>
        <div class="d-flex justify-content-between mb-3">
            <strong>Total:</strong>
            <span id="total-price">Rp 0</span>
        </div>
        <button class="btn btn-success w-100 mb-3" onclick="checkout()">
            <i class="bi bi-credit-card"></i> Checkout
        </button>
        <button class="btn btn-outline-secondary w-100" onclick="toggleCart()">
            Lanjut Belanja
        </button>
    </div>

    <!-- Kalimat Gemini API -->
    <div class="d-flex gap-2">
        <div class="marquee flex-fill">
            <marquee behavior="scroll" direction="left" scrollamount="6">{{ $kalimat1 }}</marquee>
        </div>
        <div class="marquee flex-fill">
            <marquee behavior="scroll" direction="left" scrollamount="6">{{ $kalimat2 }}</marquee>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="welcome-section text-center">
        <div class="container">
            <h1>Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p>Temukan berbagai produk berkualitas hanya di MP Mart</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Products Grid -->
            <div class="col-12">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($barangs as $barang)
                    <div class="col">
                        <div class="card h-100">
                            <img src="{{ $barang->foto ? asset('storage/' . $barang->foto) : ($barang->gambar ? asset('storage/' . $barang->gambar) : 'https://via.placeholder.com/300x200') }}" 
                                 class="card-img-top" 
                                 alt="{{ $barang->nama_barang }}"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="product-title">{{ $barang->nama_barang }}</h5>
                                <p class="product-price mb-3">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</p>
                                <p class="text-muted mb-3">
                                    <i class="bi bi-box-seam"></i> Stok: {{ $barang->stok }}
                                </p>
                                <div class="quantity-control mb-3">
                                    <button class="quantity-btn" onclick="decrementQuantity('{{ $barang->kode_unik }}')">-</button>
                                    <input type="number" class="quantity-input" id="quantity-{{ $barang->kode_unik }}" value="1" min="1" max="{{ $barang->stok }}" onchange="updateQuantity('{{ $barang->kode_unik }}')">
                                    <button class="quantity-btn" onclick="incrementQuantity('{{ $barang->kode_unik }}', {{ $barang->stok }})">+</button>
                                </div>
                                <button class="btn btn-add-cart w-100" 
                                        onclick="addToCart('{{ $barang->kode_unik }}', {{ $barang->harga_jual }}, '{{ addslashes($barang->nama_barang) }}', '{{ $barang->tipe }}')">
                                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        let cartTotal = 0;

        function toggleCart() {
            document.getElementById('cartModal').classList.toggle('active');
            document.getElementById('cartOverlay').classList.toggle('active');
        }

        function incrementQuantity(kode, maxStock) {
            const input = document.getElementById(`quantity-${kode}`);
            const currentValue = parseInt(input.value);
            if (currentValue < maxStock) {
                input.value = currentValue + 1;
                updateQuantity(kode);
            }
        }

        function decrementQuantity(kode) {
            const input = document.getElementById(`quantity-${kode}`);
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantity(kode);
            }
        }

        function updateQuantity(kode) {
            const input = document.getElementById(`quantity-${kode}`);
            const value = parseInt(input.value);
            if (value < 1) input.value = 1;
            if (value > parseInt(input.max)) input.value = input.max;
        }

        function addToCart(kode, price, name, tipe) {
            const quantity = parseInt(document.getElementById(`quantity-${kode}`).value);
            const existingItem = cart.find(item => item.kode === kode && item.tipe === tipe);

            if (existingItem) {
                existingItem.quantity += quantity;
                existingItem.subtotal = existingItem.quantity * price;
            } else {
                cart.push({
                    kode: kode,
                    tipe: tipe,
                    name: name,
                    quantity: quantity,
                    price: price,
                    subtotal: quantity * price,
                    harga_jual: price
                });
            }

            updateCartDisplay();
            document.getElementById(`quantity-${kode}`).value = 1;
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartContainer = document.getElementById('cart-items');
            cartContainer.innerHTML = '';
            cartTotal = 0;

            cart.forEach((item, index) => {
                cartTotal += item.subtotal;
                const itemElement = document.createElement('div');
                itemElement.className = 'd-flex justify-content-between align-items-start mb-3';
                itemElement.innerHTML = `
                    <div class="me-3">
                        <div class="fw-bold mb-1">${item.name}</div>
                        <div class="text-muted small">
                            ${item.quantity}x @ Rp ${item.price.toLocaleString()}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-1">Rp ${item.subtotal.toLocaleString()}</div>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                cartContainer.appendChild(itemElement);
            });

            document.getElementById('cart-count').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('total-price').textContent = `Rp ${cartTotal.toLocaleString()}`;
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Keranjang belanja kosong!');
                return;
            }

            // Save cart data to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Redirect to keranjang page
            window.location.href = "{{ route('keranjang') }}";
        }
    </script>
</body>
</html>
