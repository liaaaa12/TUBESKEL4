<?php

namespace App\Filament\Resources\PembelianBarangResource\Pages;

use App\Filament\Resources\PembelianBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\PembelianBarang;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables;

class ListPembelianBarangs extends ListRecords
{
    protected static string $resource = PembelianBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getFooter(): ?\Illuminate\View\View
    {
        $grandTotal = \App\Models\PembelianBarang::sum('total');
        return view('filament.footer-grandtotal', [
            'grandTotal' => $grandTotal,
        ]);
    }
}
