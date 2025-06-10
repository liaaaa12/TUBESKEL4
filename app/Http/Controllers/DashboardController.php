<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKonsinyasi;

class DashboardController extends Controller
{
    public function index()
    {
        $apiKey = env('GEMINI_API_KEY');
        $model = 'gemini-pro';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Ambil kalimat1
        $kalimat1 = $this->getKalimatGemini($url, 'Buatkan satu kalimat pendek yang mengajak orang untuk belanja kebutuhan harian mereka di MP Mart. Jangan pernah sebut kata diskon atau promo.');

        // Ambil kalimat2
        $kalimat2 = $this->getKalimatGemini($url, 'Buatkan satu kalimat ajakan lain yang berbeda, namun tetap tentang belanja di MP Mart.');

        // Gabungkan produk
        $produkBiasa = Barang::all()->map(function ($item) {
            return [
                'kode' => $item->Kode_barang,
                'nama_barang' => $item->nama_barang,
                'foto' => $item->foto,
                'stok' => $item->stok,
                'harga' => $item->harga_barang,
                'tipe' => 'biasa'
            ];
        });

        $produkKonsinyasi = BarangKonsinyasi::all()->map(function ($item) {
            return [
                'kode' => $item->kode_barang_konsinyasi,
                'nama_barang' => $item->nama_barang,
                'foto' => $item->foto,
                'stok' => $item->stok,
                'harga' => $item->harga,
                'tipe' => 'konsinyasi'
            ];
        });

        $produkGabungan = $produkBiasa->concat($produkKonsinyasi);

        return view('customer', [
            'kalimat1' => $kalimat1,
            'kalimat2' => $kalimat2,
            'produkGabungan' => $produkGabungan
        ]);
    }

    private function getKalimatGemini($url, $prompt)
    {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $text = '';

        if (!curl_errno($ch)) {
            $result = json_decode($response, true);
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        }

        curl_close($ch);

        // Fallback jika kosong
        if (empty($text)) {
            $text = 'Belanja kebutuhan harian Anda hanya di MP Mart!';
        }

        return $text;
    }
}
