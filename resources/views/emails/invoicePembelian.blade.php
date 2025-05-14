<h2>Invoice: {{ $data['invoice_number'] }}</h2>
<p>Halo {{ $data['vendor_name'] }},</p>
<p>Invoice pembelian barang Kami terlampir dalam email ini.</p>
<p>Terima kasih telah melakukan Transaksi pembelian barang dengan toko kami.</p>
<p>Silahkan cek detail transaksi pembelian barang Kami di bawah ini:</p>

<ul>
    <li><strong>Tanggal:</strong> {{ $data['invoice_date'] }}</li>
    <li><strong>Nama Barang:</strong> {{ $data['barang_name'] }}</li>
    <li><strong>Status Pembayaran:</strong> {{ isset($data['keterangan']) ? ucfirst($data['keterangan']) : '-' }}</li>
</ul>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-top: 10px;">
    <thead>
        <tr>
            <th>Nama Barang</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['items'] as $item)
        <tr>
            <td>{{ $item['nama_barang'] }}</td>
            <td>{{ $item['qty'] }}</td>
            <td>Rp {{ number_format($item['harga'], 2, ',', '.') }}</td>
            <td>Rp {{ number_format($item['qty'] * $item['harga'], 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" align="right"><strong>Total</strong></td>
            <td><strong>Rp {{ number_format($data['total'], 2, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>
