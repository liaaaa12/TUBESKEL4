<?php

namespace App\Http\Controllers;

use App\Models\BarangKonsinyasi;
use Illuminate\Http\Request;

class BarangKonsinyasiController extends Controller
{
    public function index()
    {
        $barang = BarangKonsinyasi::all();
        return view('barang_konsinyasi.index', compact('barang'));
    }

    public function create()
    {
        return view('barang_konsinyasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer',
            'harga' => 'required|numeric',
            'pemilik' => 'required|string|max:255',
        ]);

        BarangKonsinyasi::create($request->all());

        return redirect()->route('barang_konsinyasi.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(BarangKonsinyasi $barangKonsinyasi)
    {
        return view('barang_konsinyasi.edit', compact('barangKonsinyasi'));
    }

    public function update(Request $request, BarangKonsinyasi $barangKonsinyasi)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer',
            'harga' => 'required|numeric',
            'pemilik' => 'required|string|max:255',
        ]);

        $barangKonsinyasi->update($request->all());

        return redirect()->route('barang_konsinyasi.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(BarangKonsinyasi $barangKonsinyasi)
    {
        $barangKonsinyasi->delete();
        return redirect()->route('barang_konsinyasi.index')->with('success', 'Barang berhasil dihapus.');
    }
}
