<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PembelianBarang;

class PDFController extends Controller
{
    // export pembelian barang
    public function exportPembelianBarang()
    {
        // Ambil data asli dari database
        $rawData = PembelianBarang::with(['vendorBarang', 'barang'])->get();

        // Hitung grand total dari data asli
        $grandTotal = $rawData->sum('total');

        // Mapping data untuk keperluan tampilan
        $data = $rawData->map(function($item) {
            return [
                'vendor' => $item->vendorBarang->nama_vndr_brg ?? '-',
                'barang' => $item->barang->nama_barang ?? '-',
                'stok' => $item->stok,
                'harga' => $item->harga,
                'total' => $item->total,
                'tanggal' => $item->created_at ? $item->created_at->format('d M Y H:i') : '-',
            ];
        });

        // Kirim data dan grandTotal ke view
        $pdf = Pdf::loadView('pdf.contoh', ['data' => $data, 'grandTotal' => $grandTotal]);
        return $pdf->download('pembelian-barang.pdf');
    }
}
