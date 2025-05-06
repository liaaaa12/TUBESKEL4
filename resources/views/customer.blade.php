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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="container">
            <h1>Selamat Datang di MP Mart</h1>
            <p class="mb-0">Temukan berbagai produk berkualitas dengan harga terbaik</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Products Grid -->
            <div class="col-md-8">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($barangs as $barang)
                    <div class="col">
                        <div class="card h-100">
                            <img src="{{ $barang->gambar ?? 'https://via.placeholder.com/300x200' }}" 
                                 class="card-img-top" 
                                 alt="{{ $barang->nama_barang }}">
                            <div class="card-body">
                                <h5 class="product-title">{{ $barang->nama_barang }}</h5>
                                <p class="product-price mb-3">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</p>
                                <div class="quantity-control mb-3">
                                    <button class="quantity-btn" onclick="decrementQuantity({{ $barang->id }})">-</button>
                                    <input type="number" 
                                           class="quantity-input" 
                                           id="quantity-{{ $barang->id }}" 
                                           value="1" 
                                           min="1" 
                                           max="{{ $barang->stok }}"
                                           onchange="updateQuantity({{ $barang->id }})">
                                    <button class="quantity-btn" onclick="incrementQuantity({{ $barang->id }}, {{ $barang->stok }})">+</button>
                                </div>
                                <button class="btn btn-add-cart w-100" 
                                        onclick="addToCart({{ $barang->id }}, {{ $barang->harga_jual }})">
                                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cart3"></i> Keranjang Belanja
                            <span class="cart-badge" id="cart-count">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-items">
                            <!-- Cart items will be added here dynamically -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <span id="total-price">Rp 0</span>
                        </div>
                        <button class="btn btn-success w-100" onclick="checkout()">
                            <i class="bi bi-credit-card"></i> Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        let cartTotal = 0;

        function incrementQuantity(id, maxStock) {
            const input = document.getElementById(`quantity-${id}`);
            const currentValue = parseInt(input.value);
            if (currentValue < maxStock) {
                input.value = currentValue + 1;
                updateQuantity(id);
            }
        }

        function decrementQuantity(id) {
            const input = document.getElementById(`quantity-${id}`);
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantity(id);
            }
        }

        function updateQuantity(id) {
            const input = document.getElementById(`quantity-${id}`);
            const value = parseInt(input.value);
            if (value < 1) input.value = 1;
            if (value > parseInt(input.max)) input.value = input.max;
        }

        function addToCart(id, price) {
            const quantity = parseInt(document.getElementById(`quantity-${id}`).value);
            const existingItem = cart.find(item => item.id === id);

            if (existingItem) {
                existingItem.quantity += quantity;
                existingItem.subtotal = existingItem.quantity * price;
            } else {
                cart.push({
                    id: id,
                    quantity: quantity,
                    price: price,
                    subtotal: quantity * price
                });
            }

            updateCartDisplay();
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
                itemElement.className = 'd-flex justify-content-between align-items-center mb-2';
                itemElement.innerHTML = `
                    <div>
                        <strong>${item.quantity}x</strong>
                        <span class="ms-2">Rp ${item.price.toLocaleString()}</span>
                    </div>
                    <div>
                        <span class="me-2">Rp ${item.subtotal.toLocaleString()}</span>
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

            // Implement checkout logic here
            alert('Checkout berhasil!');
            cart = [];
            updateCartDisplay();
        }
    </script>
</body>
</html>
