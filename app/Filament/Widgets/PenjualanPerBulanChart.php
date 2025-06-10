<?php 
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Penjualan;
// use App\Models\PenjualanBarang;

use Carbon\Carbon;

class PenjualanPerBulanChart extends ChartWidget
{
    // protected static ?string $heading = 'Penjualan Per Bulan '+date('Y'); // Judul widget chart
    protected static ?string $heading = null; // biarkan null

    public function getHeading(): string
    {
        return 'Penjualan Per Bulan ' . date('Y');
    }

    

    // Mendapatkan data untuk chart
    protected function getData(): array
{
    $year = now()->year;

    // Query total penjualan dari barang reguler (yang memiliki Kode_barang)
    $orders = \App\Models\PenjualanBarang::query()
        ->join('penjualan', 'penjualan.id', '=', 'penjualan_barang.penjualan_id')
        ->where('penjualan.status', 'bayar')
        ->whereNotNull('penjualan_barang.Kode_barang') // Hanya barang reguler
        ->whereYear('penjualan.tgl', $year)
        ->selectRaw('MONTH(penjualan.tgl) as month, SUM(penjualan_barang.harga_jual * penjualan_barang.jml) as total_penjualan')
        ->groupBy('month')
        ->pluck('total_penjualan', 'month');

    $allMonths = collect(range(1, 12));

    $data = $allMonths->map(fn($month) => $orders->get($month, 0));
    $labels = $allMonths->map(fn($month) => \Carbon\Carbon::create()->month($month)->locale('id')->translatedFormat('F'));

    return [
        'datasets' => [
            [
                'label' => 'Total Penjualan',
                'data' => $data,
                'backgroundColor' => '#36A2EB',
            ],
        ],
        'labels' => $labels,
    ];
}


    // Jenis chart yang digunakan, misalnya bar chart
    protected function getType(): string
    {
        return 'line'; // Tipe chart bisa diganti sesuai kebutuhan, seperti 'line', 'pie', dll.
    }
}