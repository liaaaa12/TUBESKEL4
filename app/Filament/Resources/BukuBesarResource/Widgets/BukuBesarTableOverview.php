<?php

namespace App\Filament\Resources\BukuBesarResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Jurnal;
use Carbon\Carbon;

class BukuBesarTableOverview extends Widget
{
    protected static string $view = 'filament.resources.buku-besar-resource.widgets.buku-besar-table-overview';

    protected int | string | array $columnSpan = 'full';

    public $periode_awal;
    public $periode_akhir;
    public $coa_id; // akun yang dipilih

    protected $listeners = ['filterUpdated' => 'getViewData'];

    public function mount(): void
    {
        // Default periode awal = bulan ini
        $this->periode_awal = request('periode_awal', now()->format('Y-m'));
        $this->periode_akhir = request('periode_akhir', now()->format('Y-m'));
        $this->coa_id = request('coa_id'); // default null
    }

    public function filterJurnal(): void
    {
        // Di sini kalau mau trigger refresh manual
        // $this->emit('filterUpdated');
    }

    public function getViewData(): array
    {
        $saldoAwal = 0;
        $jurnalsQuery = Jurnal::with(['jurnaldetail' => function ($query) {
            if ($this->coa_id) {
                $query->where('coa_id', $this->coa_id);
            }
            $query->with('coa');
        }])
        ->orderBy('tgl', 'asc')
        ->orderBy('id', 'asc');

        if ($this->periode_awal && $this->periode_akhir) {
            $awal = Carbon::createFromFormat('Y-m', $this->periode_awal)->startOfMonth();
            $akhir = Carbon::createFromFormat('Y-m', $this->periode_akhir)->endOfMonth();

            // Hitung saldo awal dari transaksi SEBELUM periode_awal
            $saldoAwal = Jurnal::where('tgl', '<', $awal)
            ->with(['jurnaldetail' => function ($query) {
                $query->where('coa_id', $this->coa_id);
            }])
            ->get()
            ->flatMap->jurnaldetail
            ->reduce(function ($carry, $detail) {
                return $carry + ($detail->debit - $detail->credit);
            }, 0);

            $jurnalsQuery->whereBetween('tgl', [$awal, $akhir]);
        }

        $jurnals = $jurnalsQuery->get();

        return [
            'jurnals' => $jurnals,
            'periode_awal' => $this->periode_awal,
            'periode_akhir' => $this->periode_akhir,
            'saldoAwal' => $saldoAwal,
        ];
    }
}