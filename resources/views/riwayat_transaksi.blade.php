<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - MP Mart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 600;
            color: white !important;
        }
        .transaction-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-bayar { background: #d4edda; color: #155724; }
        .status-pesan { background: #fff3cd; color: #856404; }
        .status-batal { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('customer') }}">MP Mart</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer') }}">
                            <i class="bi bi-arrow-left"></i> Kembali ke Toko
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Riwayat Transaksi</h2>

        <!-- Date Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('riwayat.transaksi') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('riwayat.transaksi') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($transaksi->isEmpty())
            <div class="alert alert-info">
                Belum ada transaksi.
            </div>
        @else
            @foreach($transaksi as $t)
                <div class="transaction-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">{{ $t->no_faktur }}</h5>
                            <p class="text-muted mb-0">
                                {{ \Carbon\Carbon::parse($t->tgl)->format('d M Y H:i') }}
                            </p>
                        </div>
                        <span class="status-badge status-{{ strtolower($t->status) }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-center">Tipe</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details[$t->id] as $item)
                                    <tr>
                                        <td>{{ $item->nama_barang }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->tipe === 'konsinyasi' ? 'bg-info' : 'bg-primary' }}">
                                                {{ ucfirst($item->tipe) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item->jml }}</td>
                                        <td class="text-end">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($t->tagihan, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="border-top pt-3">
                        <small class="text-muted">
                            @if($t->payment_type)
                                Metode Pembayaran: {{ ucfirst($t->payment_type) }}<br>
                            @endif
                            @if($t->status_message)
                                Status: {{ $t->status_message }}<br>
                            @endif
                            @if($t->transaction_time)
                                Waktu Transaksi: {{ \Carbon\Carbon::parse($t->transaction_time)->format('d M Y H:i') }}<br>
                            @endif
                            @if($t->settlement_time)
                                Waktu Pembayaran: {{ \Carbon\Carbon::parse($t->settlement_time)->format('d M Y H:i') }}
                            @endif
                        </small>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 