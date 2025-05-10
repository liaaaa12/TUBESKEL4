<?php

namespace App\Filament\Exports;

use App\Models\PembelianBarang;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PembelianBarangExporter extends Exporter
{
    protected static ?string $model = PembelianBarang::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('vendorBarang.nama_vndr_brg')->label('Vendor'),
            ExportColumn::make('barang.nama_barang')->label('Barang'),
            ExportColumn::make('stok')->label('Stok'),
            ExportColumn::make('harga')->label('Harga'),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('created_at')->label('Tanggal Pembelian'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pembelian barang export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
} 