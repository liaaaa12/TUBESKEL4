<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;

class PenjualanPerPembeliChart extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan per Pembeli';

    protected function getData(): array
    {
        $year = now()->year;
$data = Penjualan::join('penjualan_barang', 'penjualan.id', '=', 'penjualan_barang.penjualan_id')
    ->join('pembeli', 'penjualan.pembeli_id', '=', 'pembeli.id') // Tambahan ini
    ->where('penjualan.status', 'bayar')
    ->whereYear('penjualan.tgl', $year)
    ->select('pembeli.nama_pembeli as nama_pembeli', DB::raw('SUM(penjualan_barang.harga_jual * penjualan_barang.jml) as total'))
    ->groupBy('pembeli.nama_pembeli')
    ->get();


        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $data->pluck('nama_pembeli'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}