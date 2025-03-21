<?php

namespace App\Filament\Resources\VendorBarangResource\Pages;

use App\Filament\Resources\VendorBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorBarang extends ListRecords
{
    protected static string $resource = VendorBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 