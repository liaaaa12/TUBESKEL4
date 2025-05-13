<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Pembelian Barang</title>
    <style>
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .title { font-size: 24px; font-weight: bold; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #eee; padding: 8px; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="title">INVOICE PEMBELIAN BARANG</div>
        <div class="info">
            <strong>No Invoice:</strong> {{ $invoice_number }}<br>
            <strong>Nama Vendor:</strong> {{ $vendor_name }}<br>
            <strong>Tanggal:</strong> {{ $invoice_date }}<br>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga * $item->qty, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <strong>Total: </strong>
        <span class="text-right">
            {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>
</body>
</html>
