<?php

namespace App\Filament\Resources\PembayaranKonsignorResource\Pages;

use App\Filament\Resources\PembayaranKonsignorResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;
use App\Mail\BuktiBayarKonsinyasi;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\DetailPembayaranKonsignor;
use App\Models\PenjualanBarang;

class CreatePembayaranKonsignor extends CreateRecord
{
    protected static string $resource = PembayaranKonsignorResource::class;

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('bayar')
                ->label('Bayar')
                ->action('bayarKonsinyasi')
                ->color('success'),
        ];
    }

    public function bayarKonsinyasi()
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // 1. Simpan pembayaran ke database
            $pembayaran = \App\Models\PembayaranKonsignor::create([
                'konsignor_id' => $data['konsignor_id'],
                'no_pembayaran' => $data['no_pembayaran'],
                'tanggal_pembayaran' => $data['tanggal_pembayaran'],
                'total_pembayaran' => $data['total_pembayaran'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            // 2. Simpan detail barang yang dibayar
            $penjualanBarang = PenjualanBarang::with('barang_konsinyasi')
                ->whereNotNull('kode_barang_konsinyasi')
                ->where('jml', '>', 0)
                ->where('harga_beli', '>', 0)
                ->whereHas('barang_konsinyasi', function ($q) use ($data) {
                    $q->where('id_konsignor', $data['konsignor_id']);
                })
                ->whereDoesntHave('detailPembayaranKonsignor')
                ->get();

            foreach ($penjualanBarang as $item) {
                $jumlah = $item->jml;
                $harga = $item->harga_beli;
                $subtotal = $jumlah * $harga;
                
                \App\Models\DetailPembayaranKonsignor::create([
                    'pembayaran_konsignor_id' => $pembayaran->id,
                    'penjualan_barang_id' => $item->id,
                    'kode_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                    'jumlah_barang' => $jumlah,
                    'harga_beli' => $harga,
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();

            // Ambil ulang data barang yang dibayar untuk email/pdf
            $soldItemsArr = $penjualanBarang->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                    'kode_barang_konsinyasi' => optional($item->barang_konsinyasi)->kode_barang_konsinyasi ?? '-',
                    'nama_barang' => optional($item->barang_konsinyasi)->nama_barang ?? '-',
                    'jml' => $item->jml,
                    'harga' => $item->harga_beli,
                    'total_harga' => $item->jml * $item->harga_beli,
                ];
            })->toArray();

            // 3. Generate PDF
            $pdf = \PDF::loadView('emails.bukti-bayar', [
                'pembayaran' => $pembayaran,
                'soldItems' => $soldItemsArr,
            ]);

            // 4. Kirim email ke konsignor
            \Mail::to($pembayaran->konsignor->email)->send(new BuktiBayarKonsinyasi($pembayaran, $pdf, $soldItemsArr));

            // 5. Redirect/Notifikasi
            Notification::make()
                ->success()
                ->title('Pembayaran Berhasil')
                ->body('Bukti bayar telah dikirim ke email konsignor.')
                ->send();

            return $this->redirect($this->getResource()::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
        }
    }
} 