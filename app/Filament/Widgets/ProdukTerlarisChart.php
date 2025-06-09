<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\PenjualanBarang;
use App\Models\Barang;
use App\Models\BarangKonsinyasi;
use Flowframe\LaravelConstant\Models\Constant;

class ProdukTerlarisChart extends ChartWidget
{
    protected static ?string $heading = 'Produk Terlaris';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $regularProducts = PenjualanBarang::query()
            ->join('barang', 'penjualan_barang.Kode_barang', '=', 'barang.id')
            ->selectRaw('barang.nama_barang as product_name, SUM(penjualan_barang.jml) as total_sold')
            ->groupBy('barang.nama_barang')
            ->get();

        $consignmentProducts = PenjualanBarang::query()
            ->join('barang_konsinyasi', 'penjualan_barang.kode_barang_konsinyasi', '=', 'barang_konsinyasi.id')
            ->selectRaw('barang_konsinyasi.nama_barang as product_name, SUM(penjualan_barang.jml) as total_sold')
            ->groupBy('barang_konsinyasi.nama_barang')
            ->get();

        $combinedProducts = $regularProducts->concat($consignmentProducts);

        $productSales = $combinedProducts->groupBy('product_name')->map(function ($items) {
            return $items->sum('total_sold');
        })->sortDesc();

        return [
            'labels' => $productSales->keys()->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $productSales->values()->toArray(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
        ];
    }
}
