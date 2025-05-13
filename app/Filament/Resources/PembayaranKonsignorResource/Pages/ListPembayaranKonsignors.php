<?php

namespace App\Filament\Resources\PembayaranKonsignorResource\Pages;

use App\Filament\Resources\PembayaranKonsignorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranKonsignors extends ListRecords
{
    protected static string $resource = PembayaranKonsignorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 