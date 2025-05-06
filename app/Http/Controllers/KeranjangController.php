<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Keranjang;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    // Menampilkan semua barang kepada customer
    public function daftarbarang()
    {
        $barangs = Barang::all();
        return view('customer', compact('barangs'));
    }

    // Menambahkan barang ke keranjang
    public function tambahKeranjang(Request $request)
    {
        Keranjang::create([
            'user_id' => Auth::id(),
            'barang_id' => $request->barang_id,
            'jumlah' => 1 // default 1, bisa dikembangkan
        ]);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    // Menampilkan isi keranjang
    public function lihatkeranjang()
    {
        $keranjang = Keranjang::with('barang')->where('user_id', Auth::id())->get();
        return view('keranjang', compact('keranjang'));
    }

    // Menghapus barang dari keranjang
    public function hapus($barang_id)
    {
        Keranjang::where('user_id', Auth::id())
                 ->where('barang_id', $barang_id)
                 ->delete();

        return redirect()->back()->with('success', 'Barang dihapus dari keranjang');
    }

    // Menampilkan riwayat pembelian (opsional jika kamu punya model transaksi)
    public function lihatriwayat()
    {
        // return view('riwayat', ['riwayat' => ...]);
    }

    // Cek status pembayaran (misalnya via payment gateway)
    public function cek_status_pembayaran_pg()
    {
        return response()->json(['status' => 'belum dibayar']);
    }
}
