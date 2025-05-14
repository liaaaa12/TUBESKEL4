<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Penjualan</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Daftar Penjualan</h2>
    <table>
        <thead>
            <tr>
                <th>No Faktur</th>
                <th>Pembeli</th>
                <th>Status</th>
                <th>Tagihan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $item)
                <tr>
                    <td>{{ $item->no_faktur }}</td>
                    <td>{{ $item->pembeli->nama_pembeli ?? '-' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ number_format($item->tagihan, 0, ',', '.') }}</td>
                    <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
