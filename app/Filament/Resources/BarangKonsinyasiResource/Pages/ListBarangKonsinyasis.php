<?php

namespace App\Filament\Resources\BarangKonsinyasiResource\Pages;

use App\Filament\Resources\BarangKonsinyasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangKonsinyasis extends ListRecords
{
    protected static string $resource = BarangKonsinyasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
