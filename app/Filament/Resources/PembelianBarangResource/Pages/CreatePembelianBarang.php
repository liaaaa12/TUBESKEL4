<?php

namespace App\Filament\Resources\PembelianBarangResource\Pages;

use App\Filament\Resources\PembelianBarangResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\InvoiceMail;
use App\Models\VendorBarang;

class CreatePembelianBarang extends CreateRecord
{
    protected static string $resource = PembelianBarangResource::class;

    protected function afterCreate(): void
    {
        // Get the barang record
        $barang = Barang::find($this->record->barang_id);
        
        // Update the stock sesuai input stok
        $barang->stok += $this->record->stok;
        $barang->save();

        // Ambil data vendor dan barang
        $vendor = VendorBarang::find($this->record->vendor_barang_id);
        $barangData = $barang;

        // Siapkan data invoice
        $data = [
            'vendor_name' => $vendor ? $vendor->nama_vndr_brg : '-',
            'barang_name' => $barangData ? $barangData->nama_barang : '-',
            'invoice_date' => $this->record->created_at,
            'invoice_number' => 'INV-' . $this->record->id,
            'items' => [
                [
                    'nama_barang' => $barangData ? $barangData->nama_barang : '-',
                    'qty' => $this->record->stok,
                    'harga' => $this->record->harga,
                ]
            ],
            'total' => $this->record->total,
            'keterangan' => $this->record->keterangan ?? '-',
        ];

        // Generate PDF invoice (gunakan view emails/invoice)
        $pdf = Pdf::loadView('emails.invoice', ['data' => $data]);

        // Kirim email ke Mailtrap (gunakan email dummy)
        Mail::to('test@mailtrap.io')->send(new InvoiceMail($data, $pdf->output()));
    }
}
