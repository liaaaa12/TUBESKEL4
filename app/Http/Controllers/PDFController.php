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
        $data = PembelianBarang::with(['vendorBarang', 'barang'])->get()->map(function($item) {
            return [
                'vendor' => $item->vendorBarang->nama_vndr_brg ?? '-',
                'barang' => $item->barang->nama_barang ?? '-',
                'stok' => $item->stok,
                'harga' => $item->harga,
                'total' => $item->total,
                'tanggal' => $item->created_at ? $item->created_at->format('d M Y H:i') : '-',
            ];
        });

        $pdf = Pdf::loadView('pdf.contoh', ['data' => $data]);
        return $pdf->download('pembelian-barang.pdf');
    }
}
