<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MP Mart - Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .navbar-brand, .nav-link { color: white !important; }
    .nav-link:hover { color: #e0e0e0 !important; }
    .marquee-container { background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%); color: white; font-weight: 500; font-size: 1rem; display: flex; overflow: hidden; }
    .marquee-container marquee { flex: 1; padding: 5px 0; }
    .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
    .card:hover { transform: translateY(-5px); }
    .card-img-top { height: 200px; object-fit: cover; border-radius: 15px 15px 0 0; }
    .quantity-control { display: flex; align-items: center; gap: 10px; }
    .quantity-btn { width: 30px; height: 30px; border-radius: 50%; border: none; background: #667eea; color: white; font-weight: bold; cursor: pointer; }
    .quantity-btn:hover { background: #764ba2; }
    .quantity-input { width: 50px; text-align: center; border: 1px solid #e2e8f0; border-radius: 5px; padding: 5px; }
    .btn-add-cart { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 500; }
    .btn-add-cart:hover { opacity: 0.9; }
    .cart-count { position: absolute; top: -8px; right: -8px; background: #e53e3e; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; }
    .welcome-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark mb-2">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="#">MP Mart</a>
    <ul class="navbar-nav d-flex align-items-center">
      <li class="nav-item position-relative">
        <a class="nav-link" href="#" onclick="toggleCart()">
          <i class="bi bi-cart3 fs-4"></i> 
          <span class="cart-count" id="cart-count">0</span>
        </a>
      </li>
      <li class="nav-item"><a class="nav-link" href="{{ route('riwayat.transaksi') }}"><i class="bi bi-clock-history"></i> Riwayat</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('password.change') }}"><i class="bi bi-key"></i> Ubah Password</a></li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </li>
      <li class="nav-item ms-3">
        <a class="nav-link" href="https://wa.me/62895619859193" target="_blank" title="Hubungi Admin">
          <i class="bi bi-whatsapp fs-4" style="color: #25d366;"></i>
        </a>
      </li>
    </ul>
  </div>
</nav>


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

  <!-- Produk Gabungan -->
  <div class="container mb-4">
    <h4 class="mb-3">Semua Produk</h4>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      @foreach($produkGabungan as $barang)
      <div class="col">
        <div class="card h-100">
          <img src="{{ $barang['foto'] ? asset('storage/' . $barang['foto']) : 'https://via.placeholder.com/300x200' }}" class="card-img-top" alt="{{ $barang['nama_barang'] }}">
          <div class="card-body">
            <h5 class="product-title">{{ $barang['nama_barang'] }}</h5>
            <p class="product-price mb-2">Rp {{ number_format($barang['harga'], 0, ',', '.') }}</p>
            <p class="text-muted mb-2"><i class="bi bi-box-seam"></i> Stok: {{ $barang['stok'] }}</p>
            <div class="quantity-control mb-2">
              <button class="quantity-btn" onclick="decrementQuantity('{{ $barang['kode'] }}')">-</button>
              <input type="number" id="quantity-{{ $barang['kode'] }}" class="quantity-input" value="1" min="1" max="{{ $barang['stok'] }}">
              <button class="quantity-btn" onclick="incrementQuantity('{{ $barang['kode'] }}', {{ $barang['stok'] }})">+</button>
            </div>
            <button class="btn btn-add-cart w-100" onclick="addToCart('{{ $barang['kode'] }}', {{ $barang['harga'] }}, '{{ addslashes($barang['nama_barang']) }}', '{{ $barang['tipe'] }}')">
              <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
            </button>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- Tambahkan skrip JavaScript Anda di bawah ini (keranjang, dll) -->
  <script>
    let cart = [];
    let cartTotal = 0;

    function toggleCart() {
      document.getElementById('cartModal').classList.toggle('active');
      document.getElementById('cartOverlay').classList.toggle('active');
    }

    function incrementQuantity(kode, maxStock) {
      const input = document.getElementById(`quantity-${kode}`);
      if (parseInt(input.value) < maxStock) input.value++;
    }

    function decrementQuantity(kode) {
      const input = document.getElementById(`quantity-${kode}`);
      if (parseInt(input.value) > 1) input.value--;
    }

    function addToCart(kode, price, name, tipe) {
      const qty = parseInt(document.getElementById(`quantity-${kode}`).value);
      const item = cart.find(i => i.kode === kode && i.tipe === tipe);
      if (item) {
        item.quantity += qty;
        item.subtotal = item.quantity * price;
      } else {
        cart.push({ kode, name, quantity: qty, price, tipe, subtotal: qty * price });
      }
      document.getElementById(`quantity-${kode}`).value = 1;
      updateCartDisplay();
    }

    function updateCartDisplay() {
      const container = document.getElementById('cart-items');
      container.innerHTML = '';
      cartTotal = 0;
      cart.forEach((item, i) => {
        cartTotal += item.subtotal;
        container.innerHTML += `
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <div class="fw-bold">${item.name}</div>
              <div class="text-muted small">${item.quantity}x @ Rp ${item.price.toLocaleString()}</div>
            </div>
            <div class="text-end">
              <div>Rp ${item.subtotal.toLocaleString()}</div>
              <button class="btn btn-sm btn-danger mt-1" onclick="removeFromCart(${i})"><i class="bi bi-trash"></i></button>
            </div>
          </div>`;
      });
      document.getElementById('cart-count').textContent = cart.reduce((sum, i) => sum + i.quantity, 0);
    }
  </script>
</body>
</html>
