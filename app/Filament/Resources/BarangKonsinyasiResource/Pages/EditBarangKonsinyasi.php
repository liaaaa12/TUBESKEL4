<?php

namespace App\Filament\Resources\BarangKonsinyasiResource\Pages;

use App\Filament\Resources\BarangKonsinyasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangKonsinyasi extends EditRecord
{
    protected static string $resource = BarangKonsinyasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
