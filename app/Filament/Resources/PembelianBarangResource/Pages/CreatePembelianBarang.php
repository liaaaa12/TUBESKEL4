<?php

namespace App\Filament\Resources\PembelianBarangResource\Pages;

use App\Filament\Resources\PembelianBarangResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\InvoiceMailPembelian;
use App\Models\VendorBarang;

class CreatePembelianBarang extends CreateRecord
{
    protected static string $resource = PembelianBarangResource::class;

    protected function afterCreate(): void
    {
        $barang = Barang::find($this->record->barang_id);
        
        $barang->stok += $this->record->stok;
        $barang->save();

        $vendor = VendorBarang::find($this->record->vendor_barang_id);
        $barangData = $barang;

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

        $pdf = Pdf::loadView('emails.invoicePembelian', ['data' => $data]);

        Mail::to('test@mailtrap.io')->send(new InvoiceMailPembelian($data, $pdf->output()));
    }
}
