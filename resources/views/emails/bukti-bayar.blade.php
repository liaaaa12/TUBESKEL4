<h2>Bukti Pembayaran Konsinyasi</h2>
<p>No Pembayaran: {{ $pembayaran->no_pembayaran }}</p>
<p>Tanggal: {{ $pembayaran->tanggal_pembayaran }}</p>
<p>Total: Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</p>
<p><b>Jumlah data barang: {{ is_countable($soldItems) ? count($soldItems) : 0 }}</b></p>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID Penjualan</th>
            <th>ID Barang Konsinyasi</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jumlah Terjual</th>
            <th>Harga Beli</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($soldItems as $item)
        <tr>
            <td>{{ $item['id'] ?? '-' }}</td>
            <td>{{ $item['id_barang_konsinyasi'] ?? '-' }}</td>
            <td>{{ $item['kode_barang_konsinyasi'] ?? '-' }}</td>
            <td>{{ $item['nama_barang'] ?? '-' }}</td>
            <td>{{ $item['jml'] ?? '-' }}</td>
            <td>Rp {{ number_format($item['harga'] ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_harga'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
