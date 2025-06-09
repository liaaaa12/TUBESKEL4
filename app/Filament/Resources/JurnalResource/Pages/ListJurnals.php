<?php

namespace App\Filament\Resources\JurnalResource\Pages;

use App\Filament\Resources\JurnalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\JurnalService;

// tambahan
use App\Filament\Resources\JurnalResource\Widgets\JurnalTableOverview;
class ListJurnals extends ListRecords
{
    protected static string $resource = JurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // tambahan
    protected function getHeaderWidgets(): array
    {
        return [
            JurnalTableOverview::class,
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->action(function ($record) {
                    // Gunakan JurnalService untuk delete jurnal
                    $jurnalService = app(JurnalService::class);
                    $jurnalService->deleteJurnal($record);

                    // Opsional: kirim notifikasi sukses
                    // \Filament\Notifications\Notification::make()
                    //     ->title('Jurnal berhasil dihapus')
                    //     ->success()
                    //     ->send();
                })
                ->successNotificationTitle('Jurnal berhasil dihapus'),
        ];
    }
}
 