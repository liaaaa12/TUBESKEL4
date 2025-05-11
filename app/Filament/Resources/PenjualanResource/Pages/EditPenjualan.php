<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPenjualan extends EditRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    // Redirect after saving
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    // Tampilkan notifikasi sukses
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data penjualan berhasil diperbarui')
            ->body('Transaksi penjualan telah berhasil diubah.');
    }
}