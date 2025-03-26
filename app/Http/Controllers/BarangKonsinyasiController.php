<?php

namespace App\Http\Controllers;

use App\Models\BarangKonsinyasi;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;

use Illuminate\Foundation\Http\FormRequest;

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

    public function store(StoreBarangKonsinyasiRequest $request)
    {
        $validated = $request->validate([
            'kode_barang_konsinyasi' => 'required',
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

    public function update(UpdateBarangKonsinyasiRequest $request, BarangKonsinyasi $barangKonsinyasi)
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
