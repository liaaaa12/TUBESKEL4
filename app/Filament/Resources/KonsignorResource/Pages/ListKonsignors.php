<?php

namespace App\Filament\Resources\KonsignorResource\Pages;

use App\Filament\Resources\KonsignorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKonsignors extends ListRecords
{
    protected static string $resource = KonsignorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
