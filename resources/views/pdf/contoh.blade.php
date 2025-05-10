<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Daftar Pembelian Barang</h2>
    <table>
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Barang</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Total</th>
                <th>Tanggal Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['vendor'] }}</td>
                <td>{{ $row['barang'] }}</td>
                <td>{{ $row['stok'] }}</td>
                <td>Rp {{ number_format($row['harga'], 2, ',', '.') }}</td>
                <td>Rp {{ number_format($row['total'], 2, ',', '.') }}</td>
                <td>{{ $row['tanggal'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 