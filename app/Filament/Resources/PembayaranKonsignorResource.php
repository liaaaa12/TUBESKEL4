<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranKonsignorResource\Pages;
use App\Models\PembayaranKonsignor;
use App\Models\PenjualanBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use App\Models\BarangKonsinyasi;
use App\Models\DetailPembayaranKonsignor;

class PembayaranKonsignorResource extends Resource
{
    protected static ?string $model = PembayaranKonsignor::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pembayaran Konsignor';

    protected static ?string $modelLabel = 'Pembayaran Konsignor';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Pilih Konsignor')
                        ->schema([
                            Forms\Components\Select::make('konsignor_id')
                                ->relationship('konsignor', 'nama')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get = null) {
                                    if ($state) {
                                        $soldItems = PenjualanBarang::with('barang_konsinyasi')
                                            ->whereNotNull('kode_barang_konsinyasi')
                                            ->whereHas('barang_konsinyasi', function ($q) use ($state) {
                                                $q->where('id_konsignor', $state);
                                            })
                                            ->where('jml', '>', 0)
                                            ->whereDoesntHave('detailPembayaranKonsignor')
                                            ->get();
                                            $soldItemsArr = $soldItems->filter(function ($item) {
                                                // Hanya ambil data yang field-nya tidak null/0
                                                return $item->kode_barang_konsinyasi && $item->jml && $item->harga_beli;
                                            })->map(function ($item) {
                                                return [
                                                    'id' => $item->id,
                                                    'kode_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                                                    'nama_barang' => $item->barang_konsinyasi->nama_barang ?? '',
                                                    'jml' => $item->jml,
                                                    'harga' => $item->harga_beli,
                                                    'total_harga' => $item->jml * $item->harga_beli,
                                                ];
                                            })->toArray();
                                        $set('sold_items', $soldItemsArr);

                                        // Perbaikan: gunakan tanggal dari $get('tanggal_pembayaran')
                                        $tanggal = $get ? $get('tanggal_pembayaran') : null;
                                        if ($tanggal && $soldItemsArr && count($soldItemsArr) > 0) {
                                            $today = \Carbon\Carbon::parse($tanggal)->format('Ymd');
                                            $count = \App\Models\PembayaranKonsignor::whereDate('tanggal_pembayaran', $tanggal)->count() + 1;
                                            $kode = 'PK-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                                            $set('no_pembayaran', $kode);

                                            $total = collect($soldItemsArr)->sum('total_harga');
                                            $set('total_pembayaran', $total);
                                        } else {
                                            $set('no_pembayaran', null);
                                            $set('total_pembayaran', null);
                                        }
                                    }
                                })
                                ->label('Konsignor'),
                            Forms\Components\Hidden::make('sold_items')->dehydrated(true),
                        ]),
                    Forms\Components\Wizard\Step::make('Barang Konsinyasi Terjual')
                        ->schema([
                            Forms\Components\DatePicker::make('tanggal_pembayaran')
                                ->required()
                                ->label('Tanggal Pembayaran')
                                ->default(now())
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $konsignorId = $get('konsignor_id');
                                    $soldItems = $get('sold_items');
                                    if ((!$soldItems || count($soldItems) == 0) && $konsignorId) {
                                        $soldItems = \App\Models\PenjualanBarang::with('barang_konsinyasi')
                                            ->whereNotNull('kode_barang_konsinyasi')
                                            ->whereHas('barang_konsinyasi', function ($q) use ($konsignorId) {
                                                $q->where('id_konsignor', $konsignorId);
                                            })
                                            ->where('jml', '>', 0)
                                            ->whereDoesntHave('detailPembayaranKonsignor', function ($query) {
                                                $query->whereHas('pembayaranKonsignor', function ($q) {
                                                    $q->whereNotNull('id');
                                                });
                                            })
                                            ->get();
                                        $soldItemsArr = $soldItems->filter(function ($item) {
                                            // Hanya ambil data yang field-nya tidak null/0
                                            return $item->kode_barang_konsinyasi && $item->jml && $item->harga_beli;
                                        })->map(function ($item) {
                                            return [
                                                'id' => $item->id,
                                                'kode_barang_konsinyasi' => $item->kode_barang_konsinyasi,
                                                'nama_barang' => $item->barang_konsinyasi->nama_barang ?? '',
                                                'jml' => $item->jml,
                                                'harga' => $item->harga_beli,
                                                'total_harga' => $item->jml * $item->harga_beli,
                                            ];
                                        })->toArray();
                                        $set('sold_items', $soldItemsArr);

                                        // Set detail pembayaran
                                        $detailPembayaran = collect($soldItemsArr)->map(function ($item) {
                                            return [
                                                'penjualan_barang_id' => $item['id'],
                                                'kode_barang_konsinyasi' => $item['kode_barang_konsinyasi'],
                                                'jumlah_pembayaran' => $item['total_harga'],
                                            ];
                                        })->toArray();
                                        $set('detailPembayaran', $detailPembayaran);
                                    }
                                    if ($konsignorId && $state && $soldItems && count($soldItems) > 0) {
                                        $total = collect($soldItems)->sum('total_harga');
                                        $set('total_pembayaran', $total);

                                        $today = \Carbon\Carbon::parse($state)->format('Ymd');
                                        $count = \App\Models\PembayaranKonsignor::whereDate('tanggal_pembayaran', $state)->count() + 1;
                                        $kode = 'PK-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                                        $set('no_pembayaran', $kode);
                                    } else {
                                        $set('no_pembayaran', null);
                                        $set('total_pembayaran', null);
                                        $set('detailPembayaran', []);
                                    }
                                }),
                            Forms\Components\Repeater::make('sold_items')
                                ->label('List Barang Terjual')
                                ->schema([
                                    Forms\Components\TextInput::make('kode_barang_konsinyasi')->label('Kode Barang')->disabled(),
                                    Forms\Components\TextInput::make('nama_barang')->label('Nama Barang')->disabled(),
                                    Forms\Components\TextInput::make('jml')->label('Jumlah Terjual')->disabled(),
                                    Forms\Components\TextInput::make('harga')->label('Harga Beli')->disabled(),
                                    Forms\Components\TextInput::make('total_harga')->label('Total')->disabled(),
                                ])
                                ->columns(5),
                            Forms\Components\TextInput::make('total_pembayaran')
                                ->label('Total Pembayaran')
                                ->disabled()
                                ->dehydrated(true)
                                ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : null),
                            Forms\Components\TextInput::make('no_pembayaran')
                                ->required()
                                ->label('No Pembayaran')
                                ->disabled()
                                ->dehydrated(true),
                            Forms\Components\Hidden::make('detailPembayaran')
                                ->dehydrated(true),
                        ]),
                ])                ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_pembayaran')
                    ->searchable()
                    ->sortable()
                    ->label('No Pembayaran'),
                Tables\Columns\TextColumn::make('konsignor.nama')
                    ->searchable()
                    ->sortable()
                    ->label('Konsignor'),
                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('total_pembayaran')
                    ->money('IDR')
                    ->sortable()
                    ->label('Total'),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayaranKonsignors::route('/'),
            'create' => Pages\CreatePembayaranKonsignor::route('/create'),
            'edit' => Pages\EditPembayaranKonsignor::route('/{record}/edit'),
        ];
    }

    public function barangKonsinyasi()
    {
        return $this->belongsTo(BarangKonsinyasi::class, 'kode_barang_konsinyasi');
    }

    public function bayarKonsinyasi()
    {
        // 1. Simpan data pembayaran konsignor
        $pembayaran = PembayaranKonsignor::create([
            'konsignor_id' => $this->konsignor_id,
            'tanggal_pembayaran' => $this->tanggal_pembayaran,
            'total_pembayaran' => $this->total_pembayaran,
            'no_pembayaran' => $this->no_pembayaran,
        ]);

        // 2. Simpan detail barang yang dibayar
        if (isset($this->sold_items) && is_array($this->sold_items)) {
            foreach ($this->sold_items as $item) {
                DetailPembayaranKonsignor::create([
                    'pembayaran_konsignor_id' => $pembayaran->id,
                    'penjualan_barang_id' => $item['id'],
                    'kode_barang_konsinyasi' => $item['kode_barang_konsinyasi'],
                    'jumlah_barang' => $item['jml'],
                    'harga_beli' => $item['harga'],
                    'subtotal' => $item['total_harga'],
                ]);
            }
        }
    }
} 