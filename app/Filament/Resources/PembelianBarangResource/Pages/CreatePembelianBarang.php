<?php

namespace App\Filament\Resources\PembelianBarangResource\Pages;

use App\Filament\Resources\PembelianBarangResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

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
    }
}
