<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianBarang; // Model pembelian barang
use App\Models\Pengirimanemail; // Model pencatatan pengiriman email
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembelianBarangMail; // Mailable untuk pembelian barang
use Barryvdh\DomPDF\Facade\Pdf;

class PengirimanEmailPembelianController extends Controller
{
    public static function proses_kirim_email_pembelian()
    {
        date_default_timezone_set('Asia/Jakarta');

        // 1. Query data pembelian yang statusnya sudah bayar dan belum dikirim email
        $data = DB::table('pembelian_barangs')
            ->join('vendor_barang', 'pembelian_barangs.vendor_barang_id', '=', 'vendor_barang.id')
            ->join('barang', 'pembelian_barangs.barang_id', '=', 'barang.id')
            ->select(
                'pembelian_barangs.id',
                'pembelian_barangs.vendor_barang_id',
                'pembelian_barangs.barang_id',
                'pembelian_barangs.created_at',
                'barang.nama_barang',
                'vendor_barang.nama_vndr_brg'
            )
            ->get();

        foreach ($data as $p) {
            $id = $p->id;
            // $email = $p->email;

            // Data untuk PDF dan email
            $items = [
                (object) [
                    'nama_barang' => $p->nama_barang,
                    'qty' => 1, // default 1, sesuaikan jika ada field qty
                    'harga' => 0, // default 0, sesuaikan jika ada field harga
                ]
            ];
            $total = 0; // default 0, sesuaikan jika ada field harga
            if (isset($p->harga)) {
                $items[0]->harga = $p->harga;
                $total = $p->harga;
            }
            if (isset($p->qty)) {
                $items[0]->qty = $p->qty;
                $total = $p->harga * $p->qty;
            }
            $dataAtributPembelian = [
                'vendor_name' => $p->nama_vndr_brg,
                'barang_name' => $p->nama_barang,
                'invoice_date' => $p->created_at,
                'invoice_number' => 'INV-' . $p->id,
                'items' => $items,
                'total' => $total,
            ];

            // Generate PDF invoice
            $pdf = Pdf::loadView('pdf.invoice', $dataAtributPembelian);

            // Kirim email menggunakan Mailable
            //Mail::to($email)->send(new PembelianBarangMail($dataAtributPembelian, $pdf->output()));

            // Delay 5 detik sebelum lanjut ke email berikutnya
            sleep(5);

            // Catat pengiriman email
            Pengirimanemail::create([
                'vendor_barang_id' => $p->vendor_barang_id ?? null,
                'barang_id' => $p->barang_id ?? null,
                'stok' => $p->stok ?? 0,
                'harga' => $p->harga ?? 0,
                'total' => $p->total ?? 0,
                // 'tgl_pengiriman_pesan' => now(), // hapus karena tidak ada di tabel
            ]);
        }

        // Dibungkus autorefresh
        return view('autorefresh_email');
    }
}
