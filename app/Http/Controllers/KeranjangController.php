<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Barang;
use App\Models\BarangKonsinyasi;
use App\Models\Penjualan;
use App\Models\PenjualanBarang;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KeranjangController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Menampilkan semua barang kepada customer
    public function daftarbarang()
    {
        $barangs = Barang::all();
        return view('customer', compact('barangs'));
    }

    // Menambahkan barang ke keranjang
    public function tambahKeranjang(Request $request)
    {
        Keranjang::create([
            'user_id' => Auth::id(),
            'barang_id' => $request->barang_id,
            'jumlah' => 1 // default 1, bisa dikembangkan
        ]);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    // Menampilkan isi keranjang
    public function lihatkeranjang()
    {
        $keranjang = Keranjang::with('barang')->where('user_id', Auth::id())->get();
        return view('keranjang', compact('keranjang'));
    }

    // Menghapus barang dari keranjang
    public function hapus($barang_id)
    {
        Keranjang::where('user_id', Auth::id())
                 ->where('barang_id', $barang_id)
                 ->delete();

        return redirect()->back()->with('success', 'Barang dihapus dari keranjang');
    }

    // Menampilkan riwayat pembelian (opsional jika kamu punya model transaksi)
    public function lihatriwayat()
    {
        // return view('riwayat', ['riwayat' => ...]);
    }

    // Cek status pembayaran (misalnya via payment gateway)
    public function cek_status_pembayaran_pg()
    {
        try {
            Log::info('Starting payment status check');
            
            // Ambil semua pembayaran yang pending
            $pembayaranPending = Pembayaran::where('jenis_pembayaran', 'pg')
                ->where(function($query) {
                    $query->where('status_code', '!=', '200')
                          ->orWhereNull('status_code');
                })
                ->orderBy('tgl_bayar', 'desc')
                ->get();

            Log::info('Found pending payments:', ['count' => $pembayaranPending->count()]);

            foreach($pembayaranPending as $pembayaran) {
                Log::info('Processing payment:', [
                    'order_id' => $pembayaran->order_id,
                    'penjualan_id' => $pembayaran->penjualan_id,
                    'current_status_code' => $pembayaran->status_code
                ]);

                if (!$pembayaran->order_id) {
                    Log::info('Skipping - No order_id');
                    continue;
                }

                // Setup cURL request ke Midtrans
                $ch = curl_init(); 
                $serverKey = config('midtrans.server_key');
                $URL = 'https://api.sandbox.midtrans.com/v2/'.$pembayaran->order_id.'/status';
                
                curl_setopt($ch, CURLOPT_URL, $URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $serverKey.":");  
                
                $output = curl_exec($ch); 
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);    
                
                $outputjson = json_decode($output, true);
                Log::info('Midtrans API Response:', [
                    'http_code' => $httpCode,
                    'response' => $outputjson
                ]);

                // Handle transaction not found (404) or expired/cancelled/denied transactions
                if ($httpCode == 404 || 
                    (isset($outputjson['transaction_status']) && 
                    in_array($outputjson['transaction_status'], ['expire', 'cancel', 'deny']))) {
                    
                    Log::info('Transaction expired/cancelled/not found. Resetting payment data.');
                    
                    // Reset payment data to initial state
                    $pembayaran->update([
                        'status_code' => null,
                        'transaction_time' => null,
                        'settlement_time' => null,
                        'status_message' => null,
                        'payment_type' => null,
                        'merchant_id' => null,
                        'gross_amount' => 0,
                        'transaction_id' => null,
                        'order_id' => null
                    ]);

                    // Get associated penjualan record
                    $penjualan = Penjualan::find($pembayaran->penjualan_id);
                    if ($penjualan) {
                        $penjualan->update(['status' => 'batal']);
                        // Restore stock
                        $this->restoreStock($penjualan->id);
                    }

                    continue;
                }

                // For valid transactions, update payment record
                $pembayaran->update([
                    'status_code' => $outputjson['status_code'] ?? null,
                    'transaction_time' => $outputjson['transaction_time'] ?? null,
                    'settlement_time' => $outputjson['settlement_time'] ?? null,
                    'status_message' => $outputjson['status_message'] ?? null,
                    'payment_type' => $outputjson['payment_type'] ?? null,
                    'merchant_id' => $outputjson['merchant_id'] ?? null,
                    'gross_amount' => $outputjson['gross_amount'] ?? $pembayaran->gross_amount
                ]);

                // Get penjualan record
                $penjualan = Penjualan::find($pembayaran->penjualan_id);
                if (!$penjualan) {
                    Log::error('Penjualan not found:', ['penjualan_id' => $pembayaran->penjualan_id]);
                    continue;
                }

                // Update status penjualan based on transaction status
                $transaction_status = $outputjson['transaction_status'] ?? '';
                $status_code = $outputjson['status_code'] ?? '';
                
                Log::info('Analyzing transaction status:', [
                    'transaction_status' => $transaction_status,
                    'status_code' => $status_code,
                    'current_penjualan_status' => $penjualan->status
                ]);

                // Update penjualan status
                if ($status_code == '200') {
                    Log::info('Payment verified (status_code 200) - marking as paid');
                    $penjualan->update(['status' => 'bayar']);
                } else if ($transaction_status == 'pending') {
                    Log::info('Payment pending');
                    $penjualan->update(['status' => 'pesan']);
                }
            }

            return response()->json(['success' => true, 'message' => 'Status pembayaran berhasil diperbarui']);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function createTransaction(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi input
            if (!$request->has('cart_data')) {
                throw new \Exception('Data keranjang tidak ditemukan');
            }

            $cart = json_decode($request->cart_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Format data keranjang tidak valid');
            }

            if (empty($cart)) {
                throw new \Exception('Keranjang belanja kosong');
            }

            // Get user and pembeli data
            $id_user = Auth::user()->id;
            $pembeli = DB::table('pembeli')
                        ->where('user_id', $id_user)
                        ->select('id')
                        ->first();

            if (!$pembeli) {
                throw new \Exception('Data pembeli tidak ditemukan');
            }

            $id_pembeli = $pembeli->id;

            // Cek apakah ada penjualan yang belum dibayar
            $penjualanExist = DB::table('penjualan')
                ->join('pembayaran', 'penjualan.id', '=', 'pembayaran.penjualan_id')
                ->where('penjualan.pembeli_id', $id_pembeli)
                ->whereNotIn('penjualan.status', ['batal', 'bayar'])
                ->where(function($query) {
                    $query->where('pembayaran.gross_amount', 0)
                          ->orWhere(function($q) {
                              $q->where('pembayaran.status_code', '!=', 200)
                                ->where('pembayaran.jenis_pembayaran', 'pg')
                                ->whereNotIn('pembayaran.status_message', ['Pembayaran expire', 'Pembayaran cancel', 'Pembayaran deny']);
                          });
                })
                ->select('penjualan.id')
                ->first();

            if ($penjualanExist) {
                return response()->json([
                    'error' => true,
                    'message' => 'Anda masih memiliki transaksi yang belum dibayar. Selesaikan pembayaran sebelumnya sebelum checkout lagi.'
                ], 400);
            }

            // Buat penjualan baru
            $penjualan = Penjualan::create([
                'no_faktur'   => Penjualan::getKodeFaktur(),
                'tgl'         => now(),
                'pembeli_id'  => $id_pembeli,
                'tagihan'     => 0,
                'status'      => 'pesan',
            ]);

            // Buat pembayaran baru
            $pembayaran = Pembayaran::create([
                'penjualan_id'      => $penjualan->id,
                'tgl_bayar'         => now(),
                'jenis_pembayaran'  => 'pg',
                'gross_amount'      => 0,
            ]);

            // Array untuk menyimpan detail barang untuk kemungkinan restore stok
            $itemDetails = [];

            // Proses setiap item di cart
            foreach ($cart as $item) {
                if (!isset($item['kode']) || !isset($item['quantity'])) {
                    throw new \Exception('Data item tidak lengkap');
                }

                // Cek tipe barang (konsinyasi atau biasa)
                if (isset($item['tipe']) && $item['tipe'] === 'konsinyasi') {
                    $barang = BarangKonsinyasi::where('kode_barang_konsinyasi', $item['kode'])->first();
                    if (!$barang) {
                        throw new \Exception("Barang konsinyasi dengan kode {$item['kode']} tidak ditemukan");
                    }
                    $kode_barang_konsinyasi = $barang->id;
                    $Kode_barang = null;
                    $harga_beli = $barang->harga;
                    $harga_jual = $barang->harga * 1.2;
                } else {
                    $barang = Barang::where('Kode_barang', $item['kode'])->first();
                    if (!$barang) {
                        throw new \Exception("Barang dengan kode {$item['kode']} tidak ditemukan");
                    }
                    $kode_barang_konsinyasi = null;
                    $Kode_barang = $barang->id;
                    $harga_beli = $barang->harga_barang;
                    $harga_jual = $barang->harga_barang * 1.2;
                }

                // Cek stok
                if ($barang->stok < $item['quantity']) {
                    throw new \Exception("Stok tidak mencukupi untuk barang: {$barang->nama_barang} (tersedia: {$barang->stok}, diminta: {$item['quantity']})");
                }

                // Simpan detail barang untuk kemungkinan restore stok
                $itemDetails[] = [
                    'model' => get_class($barang),
                    'id' => $barang->id,
                    'quantity' => $item['quantity']
                ];

                // Hitung subtotal
                $subtotal = $item['quantity'] * $harga_jual;

                // Tambahkan ke penjualan_barang
                DB::table('penjualan_barang')->insert([
                    'penjualan_id' => $penjualan->id,
                    'kode_barang_konsinyasi' => $kode_barang_konsinyasi,
                    'Kode_barang' => $Kode_barang,
                    'jml' => $item['quantity'],
                    'harga_beli' => $harga_beli,
                    'harga_jual' => $harga_jual,
                    'subtotal' => $subtotal,
                    'tgl' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Kurangi stok
                $barang->stok -= $item['quantity'];
                $barang->save();

                Log::info("Stok barang {$barang->nama_barang} dikurangi sebanyak {$item['quantity']}");
            }

            // Simpan detail barang di session untuk kemungkinan restore
            session(['pending_transaction_items' => $itemDetails]);

            // Update total tagihan
            $total = DB::table('penjualan_barang')
                ->where('penjualan_id', $penjualan->id)
                ->sum(DB::raw('harga_jual * jml'));
            
            $penjualan->tagihan = $total;
            $penjualan->save();

            // Generate Midtrans token
            $order_id = $penjualan->no_faktur . '-' . date('YmdHis');
            $items = [];
            foreach ($cart as $item) {
                $items[] = [
                    'id'       => $item['kode'],
                    'price'    => $item['price'],
                    'quantity' => $item['quantity'],
                    'name'     => $item['name']
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id'      => $order_id,
                    'gross_amount'  => $total,
                ],
                'item_details'      => $items,
                'customer_details'  => [
                    'first_name' => Auth::user()->name,
                    'email'      => Auth::user()->email,
                ],
                'expiry' => [
                    'start_time' => date("Y-m-d H:i:s O"),
                    'unit'       => 'minutes',
                    'duration'   => 2
                ],
                'finish_redirect_url' => route('customer'),    // Untuk pembayaran sukses
                'unfinish_redirect_url' => route('customer'), // Untuk pembayaran pending
                'error_redirect_url' => route('customer')      // Untuk pembayaran error/expired
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                Log::error('Midtrans Error: ' . $e->getMessage());
                throw new \Exception('Gagal mendapatkan token pembayaran: ' . $e->getMessage());
            }

            // Update pembayaran
            Pembayaran::where('penjualan_id', $penjualan->id)
                ->update([
                    'order_id'         => $order_id,
                    'gross_amount'     => $total,
                    'status_code'      => '201',
                    'status_message'   => 'Pending payment',
                    'transaction_id'   => $snapToken
                ]);

            DB::commit();
            Log::info("Transaksi berhasil dibuat: {$order_id}");
            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order_id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Midtrans Callback Received:', $payload);

            $orderId = $payload['order_id'];
            $transactionStatus = $payload['transaction_status'];
            
            // Find the payment record
            $pembayaran = Pembayaran::where('order_id', $orderId)->first();
            if (!$pembayaran) {
                throw new \Exception("Pembayaran dengan order ID {$orderId} tidak ditemukan");
            }

            // Update payment record
            $pembayaran->update([
                'status_code' => $payload['status_code'],
                'transaction_time' => $payload['transaction_time'] ?? null,
                'settlement_time' => $payload['settlement_time'] ?? null,
                'status_message' => $payload['status_message'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'merchant_id' => $payload['merchant_id'] ?? null,
                'gross_amount' => $payload['gross_amount']
            ]);

            // Get associated penjualan record
            $penjualan = Penjualan::find($pembayaran->penjualan_id);
            if (!$penjualan) {
                throw new \Exception("Penjualan tidak ditemukan untuk pembayaran ID: {$pembayaran->id}");
            }

            // Update penjualan status based on transaction status
            if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                $penjualan->update(['status' => 'bayar']);
            } elseif ($transactionStatus == 'pending') {
                $penjualan->update(['status' => 'pesan']);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                // Restore stock for cancelled/expired transactions
                $this->restoreStock($penjualan->id);
                $penjualan->update(['status' => 'batal']);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Callback Error: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    private function restoreStock($penjualanId)
    {
        try {
            $penjualanBarang = DB::table('penjualan_barang')
                ->where('penjualan_id', $penjualanId)
                ->get();

            foreach ($penjualanBarang as $item) {
                if ($item->kode_barang_konsinyasi) {
                    $barang = BarangKonsinyasi::find($item->kode_barang_konsinyasi);
                    if ($barang) {
                        $barang->stok += $item->jml;
                        $barang->save();
                        Log::info("Restored stock for konsinyasi item: {$item->kode_barang_konsinyasi}, amount: {$item->jml}");
                    }
                } else if ($item->Kode_barang) {
                    $barang = Barang::find($item->Kode_barang);
                    if ($barang) {
                        $barang->stok += $item->jml;
                        $barang->save();
                        Log::info("Restored stock for regular item: {$item->Kode_barang}, amount: {$item->jml}");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error restoring stock: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkStatus($orderId, Request $request)
    {
        try {
            $pembayaran = Pembayaran::where('order_id', $orderId)->first();
            if (!$pembayaran) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            $penjualan = Penjualan::find($pembayaran->penjualan_id);
            if (!$penjualan) {
                return response()->json(['error' => 'Sale not found'], 404);
            }

            // Handle forced cancellation from Return to Merchant button
            if ($request->has('force_cancel')) {
                // Update pembayaran status
                $pembayaran->update([
                    'status_code' => '202',
                    'status_message' => 'Transaction Cancelled',
                    'transaction_status' => 'cancel'
                ]);

                // Update penjualan status and restore stock
                $penjualan->update(['status' => 'batal']);
                $this->restoreStock($penjualan->id);

                return response()->json([
                    'status' => 'batal',
                    'payment_status' => 'cancelled',
                    'message' => 'Transaction has been cancelled'
                ]);
            }

            // Check Midtrans status
            $serverKey = config('midtrans.server_key');
            $URL = 'https://api.sandbox.midtrans.com/v2/'.$orderId.'/status';
            
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $serverKey.":"); 
            
            $response = curl_exec($ch); 
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            // Handle expired or cancelled transactions
            if ($httpCode == 404 || 
                (isset($responseData['transaction_status']) && 
                 in_array($responseData['transaction_status'], ['expire', 'cancel', 'deny']))) {
                
                // Update pembayaran status
                $pembayaran->update([
                    'status_code' => $responseData['status_code'] ?? '202',
                    'status_message' => $responseData['status_message'] ?? 'Transaction Expired',
                    'transaction_status' => $responseData['transaction_status'] ?? 'expire'
                ]);

                // Update penjualan status and restore stock
                $penjualan->update(['status' => 'batal']);
                $this->restoreStock($penjualan->id);

                return response()->json([
                    'status' => 'batal',
                    'payment_status' => 'expired',
                    'redirect' => route('customer')
                ]);
            }

            return response()->json([
                'status' => $penjualan->status,
                'payment_status' => $pembayaran->status_message,
                'transaction_status' => $responseData['transaction_status'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'status' => 'error',
                'redirect' => route('customer')
            ], 500);
        }
    }

    public function testSimpleCallback()
    {
        try {
            // Ambil pembayaran yang pending
            $pembayaran = Pembayaran::where('status_code', '201')->first();
            
            if (!$pembayaran) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tidak ada pembayaran pending yang ditemukan'
                ], 404);
            }

            // Buat data test
            $testData = [
                'transaction_status' => 'settlement',
                'status_code' => '200',
                'order_id' => $pembayaran->order_id,
                'gross_amount' => $pembayaran->gross_amount,
                'transaction_time' => date('Y-m-d H:i:s'),
                'payment_type' => 'bank_transfer',
                'fraud_status' => 'accept'
            ];

            // Log data test
            Log::info('Testing callback with data:', $testData);

            // Buat request dengan data test
            $request = new Request();
            $request->merge($testData);

            // Panggil handleCallback
            $result = $this->handleCallback($request);

            // Log hasil test
            Log::info('Test Callback Result:', [
                'input' => $testData,
                'output' => $result->getContent()
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Test Callback Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error saat testing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function riwayatTransaksi(Request $request)
    {
        try {
            $id_user = Auth::user()->id;
            
            // Get pembeli ID
            $pembeli = DB::table('pembeli')
                ->where('user_id', $id_user)
                ->select('id')
                ->first();

            if (!$pembeli) {
                return redirect()->route('customer')->with('error', 'Data pembeli tidak ditemukan');
            }

            // Build query
            $query = DB::table('penjualan')
                ->join('pembayaran', 'penjualan.id', '=', 'pembayaran.penjualan_id')
                ->join('pembeli', 'penjualan.pembeli_id', '=', 'pembeli.id')
                ->where('penjualan.pembeli_id', $pembeli->id);

            // Apply date filters if provided
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('penjualan.tgl', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('penjualan.tgl', '<=', $request->end_date);
            }

            // Get filtered transactions
            $transaksi = $query->select(
                    'penjualan.id',
                    'penjualan.no_faktur',
                    'penjualan.tgl',
                    'penjualan.tagihan',
                    'penjualan.status',
                    'pembayaran.payment_type',
                    'pembayaran.status_message',
                    'pembayaran.transaction_time',
                    'pembayaran.settlement_time'
                )
                ->orderBy('penjualan.tgl', 'desc')
                ->get();

            // Get details for each transaction
            $details = [];
            foreach ($transaksi as $t) {
                // Get regular items
                $regular_items = DB::table('penjualan_barang')
                    ->join('barang', 'penjualan_barang.Kode_barang', '=', 'barang.id')
                    ->where('penjualan_barang.penjualan_id', $t->id)
                    ->whereNotNull('penjualan_barang.Kode_barang')
                    ->select(
                        'barang.nama_barang',
                        'penjualan_barang.jml',
                        'penjualan_barang.harga_jual',
                        DB::raw('penjualan_barang.jml * penjualan_barang.harga_jual as subtotal'),
                        DB::raw("'regular' as tipe")
                    )
                    ->get();

                // Get konsinyasi items
                $konsinyasi_items = DB::table('penjualan_barang')
                    ->join('barang_konsinyasi', 'penjualan_barang.kode_barang_konsinyasi', '=', 'barang_konsinyasi.id')
                    ->where('penjualan_barang.penjualan_id', $t->id)
                    ->whereNotNull('penjualan_barang.kode_barang_konsinyasi')
                    ->select(
                        'barang_konsinyasi.nama_barang',
                        'penjualan_barang.jml',
                        'penjualan_barang.harga_jual',
                        DB::raw('penjualan_barang.jml * penjualan_barang.harga_jual as subtotal'),
                        DB::raw("'konsinyasi' as tipe")
                    )
                    ->get();

                // Combine both types of items
                $details[$t->id] = $regular_items->concat($konsinyasi_items);
            }

            return view('riwayat_transaksi', [
                'transaksi' => $transaksi,
                'details' => $details
            ]);

        } catch (\Exception $e) {
            Log::error('Error in riwayatTransaksi: ' . $e->getMessage());
            return redirect()->route('customer')->with('error', 'Terjadi kesalahan saat memuat riwayat transaksi');
        }
    }
}
