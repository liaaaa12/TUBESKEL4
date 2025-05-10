<?php

namespace App\Filament\Resources\PembelianBarangResource\Pages;

use App\Filament\Resources\PembelianBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembelianBarang extends EditRecord
{
    protected static string $resource = PembelianBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
