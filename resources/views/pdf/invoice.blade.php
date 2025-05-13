<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $no_faktur }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .invoice-box {
            width: 100%;
            padding: 20px;
            border: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background: #f2f2f2;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
        }
        .info {
            margin-top: 10px;
            line-height: 1.5;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f8f8;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="title">INVOICE PEMBAYARAN</div>

        <div class="info">
            <strong>No Faktur:</strong> {{ $no_faktur }}<br>
            <strong>Nama Pembeli:</strong> {{ $nama_pembeli }}<br>
            <strong>Tanggal:</strong> {{ $tanggal }}
        </div>

        @php
            $total = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $isKonsinyasi = isset($item->jenis_barang) && $item->jenis_barang === 'konsinyasi';
                        $subtotal = $item->harga_jual * $item->total_barang;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $item->nama_barang }}@if($isKonsinyasi) (Konsinyasi)@endif</td>
                        <td class="text-right">{{ rupiah($item->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-right">{{ $item->total_barang }}</td>
                        <td class="text-right">{{ rupiah($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">{{ rupiah($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top: 30px;">Terima kasih atas kepercayaan Anda!</p>
    </div>
</body>
</html>
