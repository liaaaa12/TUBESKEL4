<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\PenjualanBarang;

class PenjualanKomposisiChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Penjualan: Ritel vs Konsinyasi';

    protected function getData(): array
    {
        $ritel = PenjualanBarang::whereNotNull('Kode_barang')->sum('subtotal');
        $konsinyasi = PenjualanBarang::whereNotNull('kode_barang_konsinyasi')->sum('subtotal');

        return [
            'datasets' => [
                [
                    'data' => [$ritel, $konsinyasi],
                    'backgroundColor' => ['#36A2EB', '#FF6384'],
                ],
            ],
            'labels' => ['Ritel', 'Konsinyasi'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Ganti ke 'pie' jika ingin pie chart
    }
}
