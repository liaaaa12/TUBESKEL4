<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keranjang - MP Mart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- Midtrans Snap JS -->
    <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
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
        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .payment-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('customer') }}">MP Mart</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer') }}">
                            <i class="bi bi-arrow-left"></i> Kembali ke Toko
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-md-8">
                <h3 class="mb-4">Detail Pesanan</h3>
                <div id="cart-items">
                    <!-- Cart items will be populated here -->
                </div>
            </div>

            <!-- Payment Section -->
            <div class="col-md-4">
                <div class="payment-section">
                    <h4 class="mb-4">Pembayaran</h4>
                    <div class="mb-3">
                        <label class="form-label">Total Belanja</label>
                        <h3 id="total-price">Rp 0</h3>
                    </div>
                    <button class="btn btn-primary w-100" onclick="pay()" id="pay-button">
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get cart data from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let cartTotal = 0;

        // Display cart items
        function displayCart() {
            const cartContainer = document.getElementById('cart-items');
            cartContainer.innerHTML = '';
            cartTotal = 0;

            if (cart.length === 0) {
                cartContainer.innerHTML = '<div class="alert alert-info">Keranjang belanja kosong</div>';
                document.getElementById('pay-button').disabled = true;
                return;
            }

            cart.forEach((item, index) => {
                if (!item.kode || !item.name || !item.price || !item.quantity) {
                    console.error('Invalid item data:', item);
                    return;
                }

                cartTotal += item.subtotal;
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">${item.name}</h5>
                            <p class="mb-0 text-muted">${item.quantity}x @ Rp ${item.price.toLocaleString()}</p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-0">Rp ${item.subtotal.toLocaleString()}</h5>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(itemElement);
            });

            document.getElementById('total-price').textContent = `Rp ${cartTotal.toLocaleString()}`;
            document.getElementById('pay-button').disabled = false;
        }

        // Process payment with Midtrans
        function pay() {
            if (cart.length === 0) {
                alert('Keranjang belanja kosong!');
                return;
            }

            // Validate cart data
            const validCart = cart.every(item => 
                item.kode && 
                item.name && 
                item.price && 
                item.quantity && 
                item.subtotal
            );

            if (!validCart) {
                alert('Data keranjang tidak valid. Silakan refresh halaman dan coba lagi.');
                return;
            }

            // Send cart data to server to create transaction
            fetch('{{ route("payment.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cart_data: JSON.stringify(cart)
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.snap_token) {
                    // Open Midtrans payment popup
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            localStorage.removeItem('cart');
                            // Open autorefresh in new window/tab with order_id
                            window.open("{{ route('payment.autorefresh_penjualan') }}?order_id=" + result.order_id, "_blank");
                            // Redirect main window after short delay
                            setTimeout(() => {
                                window.location.href = "{{ route('customer') }}";
                            }, 2000);
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            alert('Pembayaran pending! Silakan selesaikan pembayaran Anda.');
                            // Open autorefresh in new window/tab with order_id
                            window.open("{{ route('payment.autorefresh_penjualan') }}?order_id=" + result.order_id, "_blank");
                        },
                        onError: function(result) {
                            console.error('Payment error:', result);
                            alert('Pembayaran gagal! Silakan coba lagi.');
                            document.getElementById('pay-button').disabled = false;
                            document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
                        },
                        onClose: function() {
                            document.getElementById('pay-button').disabled = false;
                            document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
                        }
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat memproses pembayaran');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan saat memproses pembayaran');
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
            });
        }

        function startPaymentStatusCheck(orderId) {
            console.log('Starting payment status check for order:', orderId);
            
            // Check every 5 seconds
            const interval = setInterval(() => {
                // Check payment status
                fetch(`/payment/check-status-pg`)
                    .then(response => response.json())
                    .then(checkResult => {
                        console.log('Global status check result:', checkResult);
                        
                        // Check specific order status
                        return fetch(`/payment/status/${orderId}`);
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Order status:', data);
                        if (data.status === 'bayar') {
                            clearInterval(interval);
                            localStorage.removeItem('cart');
                            window.location.href = "{{ route('customer') }}";
                        } else if (data.status === 'batal') {
                            clearInterval(interval);
                            alert('Pembayaran dibatalkan.');
                            document.getElementById('pay-button').disabled = false;
                            document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking payment status:', error);
                    });
            }, 5000);
        }

        // Initial display
        displayCart();
    </script>
</body>
</html> 