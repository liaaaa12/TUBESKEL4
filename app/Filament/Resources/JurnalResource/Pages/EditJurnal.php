<?php

namespace App\Filament\Resources\JurnalResource\Pages;

use App\Filament\Resources\JurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\JurnalService;
use Illuminate\Database\Eloquent\Model;

class EditJurnal extends EditRecord
{
    protected static string $resource = JurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $jurnalService = app(JurnalService::class);
        // Menggunakan JurnalService untuk update jurnal
        return $jurnalService->updateJurnal($record, $data);
    }
}
