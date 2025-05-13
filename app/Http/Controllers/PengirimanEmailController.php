<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengirimanemail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;

class PengirimanEmailController extends Controller
{
    public static function proses_kirim_email_pembayaran()
    {
        date_default_timezone_set('Asia/Jakarta');

        $data = DB::table('penjualan')
            ->join('pembeli', 'penjualan.pembeli_id', '=', 'pembeli.id')
            ->join('users', 'pembeli.user_id', '=', 'users.id')
            ->where('status', 'bayar')
            ->whereNotIn('penjualan.id', function ($query) {
                $query->select('penjualan_id')
                    ->from('pengirimanemail');
            })
            ->select('penjualan.id', 'penjualan.no_faktur', 'users.email', 'penjualan.pembeli_id')
            ->orderBy('penjualan.id')
            ->get();

        \Log::info('Ditemukan ' . count($data) . ' transaksi yang perlu dikirim email');

        foreach ($data as $p) {
            $id = $p->id;
            $no_faktur = $p->no_faktur;
            $email = $p->email;
            $pembeli_id = $p->pembeli_id;

            $barang_reguler = collect([]);
            try {
                $barang_reguler = DB::table('penjualan')
                    ->join('penjualan_barang', 'penjualan.id', '=', 'penjualan_barang.penjualan_id')
                    ->leftJoin('pembayaran', 'penjualan.id', '=', 'pembayaran.penjualan_id')
                    ->join('barang', 'penjualan_barang.kode_barang', '=', 'barang.id')
                    ->join('pembeli', 'penjualan.pembeli_id', '=', 'pembeli.id')
                    ->select(
                        'penjualan.id',
                        'penjualan.no_faktur',
                        'pembeli.nama_pembeli',
                        'penjualan_barang.kode_barang',
                        'barang.nama_barang',
                        'penjualan_barang.harga_jual',
                        'barang.foto',
                        DB::raw('SUM(penjualan_barang.jml) as total_barang'),
                        DB::raw('SUM(penjualan_barang.harga_jual * penjualan_barang.jml) as total_belanja'),
                        DB::raw("'reguler' as jenis_barang")
                    )
                    ->whereNull('penjualan_barang.kode_barang_konsinyasi')
                    ->where('penjualan.pembeli_id', '=', $pembeli_id)
                    ->where('penjualan.id', '=', $id)
                    ->groupBy(
                        'penjualan.id',
                        'penjualan.no_faktur',
                        'pembeli.nama_pembeli',
                        'penjualan_barang.kode_barang',
                        'barang.nama_barang',
                        'penjualan_barang.harga_jual',
                        'barang.foto'
                    )
                    ->get();

                \Log::info('Barang reguler untuk transaksi ID ' . $id . ': ' . count($barang_reguler));
            } catch (\Exception $e) {
                \Log::error('Error saat query barang reguler: ' . $e->getMessage());
                $barang_reguler = collect([]);
            }

            $barang_konsinyasi = collect([]);
            try {
                $barang_konsinyasi = DB::table('penjualan')
                    ->join('penjualan_barang', 'penjualan.id', '=', 'penjualan_barang.penjualan_id')
                    ->leftJoin('pembayaran', 'penjualan.id', '=', 'pembayaran.penjualan_id')
                    ->join('barang_konsinyasi', 'penjualan_barang.kode_barang_konsinyasi', '=', 'barang_konsinyasi.id')
                    ->join('pembeli', 'penjualan.pembeli_id', '=', 'pembeli.id')
                    ->select(
                        'penjualan.id',
                        'penjualan.no_faktur',
                        'pembeli.nama_pembeli',
                        'penjualan_barang.kode_barang_konsinyasi as kode_barang',
                        'barang_konsinyasi.nama_barang',
                        'penjualan_barang.harga_jual',
                        'barang_konsinyasi.foto',
                        DB::raw('SUM(penjualan_barang.jml) as total_barang'),
                        DB::raw('SUM(penjualan_barang.harga_jual * penjualan_barang.jml) as total_belanja'),
                        DB::raw("'konsinyasi' as jenis_barang")
                    )
                    ->whereNotNull('penjualan_barang.kode_barang_konsinyasi')
                    ->where('penjualan.pembeli_id', '=', $pembeli_id)
                    ->where('penjualan.id', '=', $id)
                    ->groupBy(
                        'penjualan.id',
                        'penjualan.no_faktur',
                        'pembeli.nama_pembeli',
                        'penjualan_barang.kode_barang_konsinyasi',
                        'barang_konsinyasi.nama_barang',
                        'penjualan_barang.harga_jual',
                        'barang_konsinyasi.foto'
                    )
                    ->get();

                \Log::info('Barang konsinyasi untuk transaksi ID ' . $id . ': ' . count($barang_konsinyasi));
            } catch (\Exception $e) {
                \Log::error('Error saat query barang konsinyasi: ' . $e->getMessage());
                $barang_konsinyasi = collect([]);
            }

            // Gabungkan dan pastikan properti bisa dibaca Blade
            $barang = collect($barang_reguler)->merge($barang_konsinyasi)->map(function ($item) {
                return (object) [
                    'kode_barang'   => $item->kode_barang,
                    'nama_barang'   => $item->nama_barang,
                    'jenis_barang'  => $item->jenis_barang ?? '-',
                    'total_barang'  => $item->total_barang,
                    'harga_jual'    => $item->harga_jual,
                    'total_belanja' => $item->total_belanja,
                    'foto'          => $item->foto ?? null,
                    'nama_pembeli'  => $item->nama_pembeli ?? '-',
                ];
            });

            if (count($barang) > 0) {
                $pdf = Pdf::loadView('pdf.invoice', [
                    'no_faktur'    => $p->no_faktur,
                    'nama_pembeli' => $barang[0]->nama_pembeli ?? '-',
                    'items'        => $barang,
                    'total'        => $barang->sum('total_belanja'),
                    'tanggal'      => now()->format('d-M-Y'),
                ]);

                $dataAtributPelanggan = [
                    'customer_name'   => $barang[0]->nama_pembeli,
                    'invoice_number'  => $p->no_faktur
                ];

                try {
                    \Log::info('Mencoba kirim email untuk transaksi ID: ' . $id . ' - Faktur: ' . $no_faktur . ' - Email: ' . $email);

                    Mail::to($email)->send(new InvoiceMail($dataAtributPelanggan, $pdf->output()));

                    \Log::info('Email berhasil dikirim untuk transaksi ID: ' . $id);

                    Pengirimanemail::create([
                        'penjualan_id' => $id,
                        'status' => 'sudah terkirim',
                        'tgl_pengiriman_pesan' => now(),
                    ]);

                    sleep(2);
                } catch (\Exception $e) {
                    \Log::error('Gagal kirim email untuk transaksi ID: ' . $id . ' - Error: ' . $e->getMessage());
                }
            }
        }

        return view('autorefresh_email');
    }
}
