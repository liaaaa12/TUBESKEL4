<?php

namespace App\Http\Livewire\PembayaranKonsignor\Pages;

use App\Models\PenjualanBarang;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreatePembayaranKonsinyonor extends Component
{
    public function render()
    {
        return view('livewire.pembayaran-konsignor.pages.create-pembayaran-konsinyonor');
    }

    public function handleFormSubmit($data)
    {
        $soldItems = [];
        if (!empty($data['penjualan_barang_ids'])) {
            $soldItems = PenjualanBarang::with('barang_konsinyasi')
                ->whereIn('id', $data['penjualan_barang_ids'])
                ->whereNotNull('kode_barang_konsinyasi')
                ->where('kode_barang_konsinyasi', '>', 0)
                ->whereHas('barang_konsinyasi', function ($q) use ($data) {
                    $q->where('id_konsignor', $data['konsignor_id']);
                })
                ->where('jml', '>', 0)
                ->whereDoesntHave('detailPembayaranKonsignor')
                ->get()
                ->map(function ($item) {
                    $barang = $item->barang_konsinyasi;
                    return [
                        'id' => $item->id,
                        'id_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                        'kode_barang_konsinyasi' => $barang ? $barang->kode_barang_konsinyasi : '[NO RELASI] ID:'.$item->kode_barang_konsinyasi,
                        'nama_barang' => $barang ? $barang->nama_barang : '[NO RELASI] ID:'.$item->kode_barang_konsinyasi,
                        'jml' => $item->jml,
                        'harga' => $barang ? $barang->harga : 0,
                        'total_harga' => $item->jml * ($barang ? $barang->harga : 0),
                    ];
                })->toArray();
        }

        // ... existing code ...
    }
} 