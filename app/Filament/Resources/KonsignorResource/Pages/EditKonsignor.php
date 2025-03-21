<?php

namespace App\Filament\Resources\KonsignorResource\Pages;

use App\Filament\Resources\KonsignorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKonsignor extends EditRecord
{
    protected static string $resource = KonsignorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
