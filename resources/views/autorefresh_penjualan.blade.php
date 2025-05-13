<!DOCTYPE html>
<html>
<head>
    <title>Auto Refresh Payment Status</title>
    <meta http-equiv="refresh" content="5">
    <script>
        // Function to check payment status
        async function checkPaymentStatus() {
            try {
                // Get all URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const orderId = urlParams.get('order_id');

                if (!orderId) {
                    console.log('Debug: No order ID found in URL');
                    return;
                }

                console.log('Debug: Checking status for order:', orderId);

                // Check payment status
                const response = await fetch(`/payment/status/${orderId}`);
                const data = await response.json();
                
                console.log('Debug: Payment status response:', data);

                // If payment is successful, close the window
                if (data.status === 'bayar') {
                    console.log('Debug: Payment completed, attempting to close window...');
                    window.close();
                } else {
                    console.log('Debug: Payment not completed yet. Current status:', data.status);
                }
            } catch (error) {
                console.error('Debug: Error checking payment status:', error);
            }
        }

        // Check status when page loads
        window.onload = checkPaymentStatus;

        // Also check status every 5 seconds
        setInterval(checkPaymentStatus, 5000);
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p>Mengecek status pembayaran...</p>
        <small>Halaman akan refresh otomatis setiap 5 detik</small>
        <br>
        <small>Halaman ini akan tertutup otomatis jika pembayaran berhasil</small>
    </div>
</body>
</html> 