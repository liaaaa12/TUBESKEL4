<?php

namespace App\Filament\Resources\PembayaranKonsignorResource\Pages;

use App\Filament\Resources\PembayaranKonsignorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembayaranKonsignor extends EditRecord
{
    protected static string $resource = PembayaranKonsignorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 