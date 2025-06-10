<?php

namespace App\Http\Controllers;

use App\Models\BarangKonsinyasi;
use App\Models\Barang;
use Illuminate\Http\Request;

class CustomerController extends Controller
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

        // Ambil data barang konsinyasi
        $barangKonsinyasi = BarangKonsinyasi::where('stok', '>', 0)->get();
        // Set harga jual konsinyasi
        foreach ($barangKonsinyasi as $bk) {
            $bk->kode_unik = $bk->kode_barang_konsinyasi;
            $bk->harga_jual = $bk->harga * 1.2;
            $bk->tipe = 'konsinyasi';
        }

        // Ambil data barang biasa
        $barangBiasa = Barang::where('stok', '>', 0)->get();
        // Set harga jual barang biasa
        foreach ($barangBiasa as $bb) {
            $bb->kode_unik = $bb->Kode_barang;
            $bb->harga_jual = $bb->harga_barang * 1.2;
            $bb->tipe = 'biasa';
        }

        // Gabungkan data
        $barangs = $barangKonsinyasi->concat($barangBiasa);
        
        // Debug untuk melihat data
        /*foreach($barangs as $barang) {
            echo "ID: " . $barang->id . "<br>";
            echo "Nama: " . $barang->nama_barang . "<br>";
            echo "Foto: " . ($barang->foto ?? $barang->gambar) . "<br>";
            echo "Harga: " . ($barang->harga ?? $barang->harga_barang) . "<br>";
            echo "Stok: " . $barang->stok . "<br>";
            echo "Tipe: " . (get_class($barang) === 'App\Models\BarangKonsinyasi' ? 'Konsinyasi' : 'Biasa') . "<br>";
            echo "<hr>";
        }
        die();*/
        
        return view('customer', compact('barangs', 'kalimat1', 'kalimat2'));
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