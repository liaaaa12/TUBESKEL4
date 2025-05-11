<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;
    
    // Redirect after saving
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    // Tampilkan notifikasi sukses
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data penjualan berhasil disimpan')
            ->body('Transaksi penjualan telah berhasil dibuat.');
    }
}