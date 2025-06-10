<?php

namespace App\Filament\Resources\BukuBesarResource\Pages;

use App\Filament\Resources\BukuBesarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

// tambahan
use App\Filament\Resources\BukuBesarResource\Widgets\BukuBesarTableOverview;

class ListBukuBesars extends ListRecords
{
    protected static string $resource = BukuBesarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    // tambahan
    protected function getHeaderWidgets(): array
    {
        return [
            BukuBesarTableOverview::class,
        ];
    }
}
