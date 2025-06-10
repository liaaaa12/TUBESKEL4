<?php

namespace App\Filament\Resources\JurnalResource\Pages;

use App\Filament\Resources\JurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\JurnalService;

class CreateJurnal extends CreateRecord
{
    protected static string $resource = JurnalResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $jurnalService = app(JurnalService::class);
        // Menggunakan JurnalService untuk membuat jurnal
        // dan mengembalikan instance jurnal yang dibuat
        return $jurnalService->createJurnal($data);
    }
}
