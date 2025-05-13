<?php

namespace App\Http\Controllers;

use App\Models\Konsignor;
use App\Models\PembayaranKonsignor;
use App\Models\PenjualanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranKonsignorController extends Controller
{
    public function index()
    {
        $pembayarans = PembayaranKonsignor::with('konsignor')->latest()->get();
        return view('pembayaran-konsignor.index', compact('pembayarans'));
    }

    public function create()
    {
        $konsignors = Konsignor::all();
        return view('pembayaran-konsignor.create', compact('konsignors'));
    }

    public function getBarangKonsinyasi($konsignorId)
    {
        $barangKonsinyasi = PenjualanBarang::with(['barang', 'penjualan'])
            ->whereHas('barang', function ($query) use ($konsignorId) {
                $query->where('konsignor_id', $konsignorId);
            })
            ->whereDoesntHave('detailPembayaranKonsignor')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->barang->nama_barang,
                    'kode_barang' => $item->barang->kode_barang,
                    'tanggal_penjualan' => $item->penjualan->tanggal_penjualan,
                    'harga_beli' => $item->harga_beli,
                    'jumlah' => $item->jumlah,
                    'total' => $item->harga_beli * $item->jumlah
                ];
            });

        return response()->json($barangKonsinyasi);
    }

    public function getSoldItems($konsignorId)
    {
        $soldItems = \App\Models\PenjualanBarang::with('barangKonsinyasi')
            ->whereHas('barangKonsinyasi', function ($q) use ($konsignorId) {
                $q->where('pemilik', $konsignorId);
            })
            ->where('jml', '>', 0)
            ->whereDoesntHave('detailPembayaranKonsignor')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode_barang_konsinyasi' => $item->barangKonsinyasi->kode_barang_konsinyasi,
                    'nama_barang' => $item->barangKonsinyasi->nama_barang,
                    'jml' => $item->jml,
                    'harga' => $item->barangKonsinyasi->harga,
                    'total_harga' => $item->jml * $item->barangKonsinyasi->harga,
                ];
            });

        return response()->json($soldItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'konsignor_id' => 'required|exists:konsignors,id',
            'tanggal_pembayaran' => 'required|date',
            'barang_ids' => 'required|array',
            'barang_ids.*' => 'exists:penjualan_barangs,id',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $noPembayaran = 'PK-' . date('Ymd') . '-' . str_pad(PembayaranKonsignor::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $totalPembayaran = PenjualanBarang::whereIn('id', $request->barang_ids)
                ->sum(DB::raw('harga_beli * jumlah'));

            $pembayaran = PembayaranKonsignor::create([
                'konsignor_id' => $request->konsignor_id,
                'no_pembayaran' => $noPembayaran,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'total_pembayaran' => $totalPembayaran,
                'keterangan' => $request->keterangan
            ]);

            // Ambil semua penjualan barang konsinyasi milik konsignor yang belum pernah dibayar
            $penjualanBarang = PenjualanBarang::with('barang_konsinyasi')
                ->whereHas('barang_konsinyasi', function ($q) use ($request) {
                    $q->where('id_konsignor', $request->konsignor_id);
                })
                ->where('jml', '>', 0)
                ->whereDoesntHave('detailPembayaranKonsignor')
                ->get();

            foreach ($penjualanBarang as $item) {
                if (empty($item->kode_barang_konsinyasi)) continue;
                $pembayaran->detailPembayaran()->create([
                    'penjualan_barang_id' => $item->id,
                    'kode_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                    'jumlah_pembayaran' => $item->jml * $item->barang_konsinyasi->harga,
                ]);
            }

            DB::commit();
            return redirect()->route('pembayaran-konsignor.index')->with('success', 'Pembayaran berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(PembayaranKonsignor $pembayaranKonsignor)
    {
        $pembayaranKonsignor->load(['konsignor', 'detailPembayaran.penjualanBarang.barang']);
        return view('pembayaran-konsignor.show', compact('pembayaranKonsignor'));
    }
} 