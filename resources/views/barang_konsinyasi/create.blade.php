@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Barang Konsinyasi</h2>
    <a href="{{ route('barang_konsinyasi.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    <form action="{{ route('barang_konsinyasi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label>Pemilik</label>
            <input type="text" name="pemilik" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
