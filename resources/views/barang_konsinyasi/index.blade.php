@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Daftar Barang Konsinyasi</h2>
    <a href="{{ route('barang_konsinyasi.create') }}" class="btn btn-primary mb-3">Tambah Barang</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Kode Barang Konsinyasi</th>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Pemilik</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barang as $key => $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->kode_barang_konsinyasi }}</td> <!-- Menambahkan kolom kode_barang_konsinyasi -->
                <td>{{ $key+1 }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->stok }}</td>
                <td>Rp {{ number_format($item->harga, 2, ',', '.') }}</td>
                <td>{{ $item->pemilik }}</td>
                <td>
                    <a href="{{ route('barang_konsinyasi.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('barang_konsinyasi.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus barang ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
