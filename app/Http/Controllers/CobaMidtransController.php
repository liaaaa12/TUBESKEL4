<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;

class CobaMidtransController extends Controller
{
    public function getSnapToken()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Contoh data transaksi
        $orderId = uniqid(); // atau bisa pakai id dari DB
        $totalHarga = 20000; // pastikan integer
        $itemHarga = 20000;  // pastikan integer

        // Siapkan parameter transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => intval($totalHarga), // wajib integer
            ],
            'item_details' => [
                [
                    'id' => 'produk-1',
                    'price' => intval($itemHarga), // wajib integer
                    'quantity' => 1,
                    'name' => 'Dimsum Bolognese',
                ],
            ],
            'customer_details' => [
                'first_name' => 'Nama',
                'email' => 'email@example.com',
                'phone' => '08123456789',
            ],
        ];

        // Dapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['snapToken' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mendapatkan token: ' . $e->getMessage()]);
        }
    }
}
