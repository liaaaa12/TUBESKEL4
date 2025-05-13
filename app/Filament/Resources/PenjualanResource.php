<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Filament\Resources\PenjualanResource\RelationManagers;
use App\Models\BarangKonsinyasi;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\SelectFilter;

use App\Models\Pembeli;
use App\Models\Barang;
use App\Models\barang_konsinyasi;
use App\Models\PenjualanBarang;

use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Pesanan')
                        ->schema([
                            Forms\Components\Section::make('Faktur')
                                ->icon('heroicon-m-document-duplicate')
                                ->schema([ 
                                    TextInput::make('no_faktur')
                                        ->default(fn () => Penjualan::getKodeFaktur())
                                        ->label('Nomor Faktur')
                                        ->required()
                                        ->readonly(),
                                    DateTimePicker::make('tgl')
                                        ->default(now())
                                        ->required(),
                                    Select::make('pembeli_id')
                                        ->label('Pembeli')
                                        ->options(Pembeli::pluck('nama_pembeli', 'id')->toArray())
                                        ->required()
                                        ->placeholder('Pilih Pembeli'),
                                    TextInput::make('tagihan')
                                        ->default(0)
                                        ->reactive()
                                        ->dehydrated(), // Ensure field is always saved
                                    TextInput::make('status')
                                        ->default('pesan')
                                        ->dehydrated(), // Ensure field is always saved
                                ])
                                ->collapsible()
                                ->columns(3),
                        ]),
                    Wizard\Step::make('Pilih Barang')
                        ->schema([
                            Repeater::make('items')
                                ->relationship('penjualanBarang')
                                ->schema([
                                    DatePicker::make('tgl')
                                        ->default(today())
                                        ->required()
                                        ->columnSpan(4),
                                    
                                    Forms\Components\Section::make('Barang Reguler')
                                        ->schema([
                                            Select::make('Kode_barang')
                                                ->label('Barang')
                                                ->options(Barang::pluck('nama_barang', 'id')->toArray())
                                                ->placeholder('Pilih Barang')
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set) {
                                                    if ($state) {
                                                        $barang = Barang::find($state);
                                                        if ($barang) {
                                                            $set('harga_jual', ($barang->harga_barang)* 120 );
                                                            $set('stok_tersedia', $barang->stok);
                                                            $set('harga_beli', $barang->harga_barang);
                                                            // Calculate subtotal when price changes
                                                            $set('subtotal', ($barang->harga_barang * 120) * 1);
                                                        }
                                                    } else {
                                                        $set('harga_jual', null);
                                                        $set('jml', 1);
                                                        $set('stok_tersedia', 0);
                                                        $set('harga_beli', 0);
                                                        $set('subtotal', 0);
                                                    }
                                                })
                                                ->searchable()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                            
                                            TextInput::make('harga_jual')
                                                ->label('Harga Satuan')
                                                ->numeric()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    // Recalculate subtotal when price changes
                                                    $jml = $get('jml') ?? 1;
                                                    $set('subtotal', ($state ?? 0) * $jml);
                                                    self::calculateGrandTotal($get, $set);
                                                })
                                                ->dehydrated(),
                                            
                                            TextInput::make('jml')
                                                ->label('Jumlah')
                                                ->default(1)
                                                ->numeric()
                                                ->minValue(1)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    $stokTersedia = $get('stok_tersedia') ?? 0;
                                                    if ($state > $stokTersedia) {
                                                        $set('jml', $stokTersedia);
                                                        $state = $stokTersedia;
                                                    }
                                                    
                                                    // Calculate subtotal when quantity changes
                                                    $hargaJual = $get('harga_jual') ?? 0;
                                                    $set('subtotal', $hargaJual * $state);
                                                    
                                                    self::calculateGrandTotal($get, $set);
                                                }),
                                            
                                            TextInput::make('harga_beli')
                                                ->label('Harga Beli')
                                                ->numeric()
                                                ->default(0)
                                                ->dehydrated(),
                                                
                                            TextInput::make('subtotal')
                                                ->label('Subtotal')
                                                ->numeric()
                                                ->default(0)
                                                ->readonly()
                                                ->dehydrated(),
                                        ])
                                        ->columns(5)
                                        ->columnSpan(8),
                                    
                                    Forms\Components\Section::make('Barang Konsinyasi')
                                        ->schema([
                                            Select::make('kode_barang_konsinyasi')
                                                ->label('Barang Konsinyasi')
                                                ->options(BarangKonsinyasi::pluck('nama_barang', 'id')->toArray())
                                                ->placeholder('Pilih Barang Konsinyasi')
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set) {
                                                    if ($state) {
                                                        $barangKonsinyasi = BarangKonsinyasi::find($state);
                                                        if ($barangKonsinyasi) {
                                                            $hargaJual = ($barangKonsinyasi->harga)*120/100;
                                                            $set('harga_jual_konsinyasi', $hargaJual);
                                                            $set('stok_tersedia_konsinyasi', $barangKonsinyasi->stok);
                                                            $set('harga_beli_konsinyasi', $barangKonsinyasi->harga);
                                                            // Add subtotal calculation for konsinyasi
                                                            $set('subtotal_konsinyasi', $hargaJual * 1);
                                                        }
                                                    } else {
                                                        $set('harga_jual_konsinyasi', null);
                                                        $set('jml_konsinyasi', 1);
                                                        $set('stok_tersedia_konsinyasi', 0);
                                                        $set('harga_beli_konsinyasi', 0);
                                                        $set('subtotal_konsinyasi', 0);
                                                    }
                                                })
                                                ->searchable()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                            
                                            TextInput::make('harga_jual_konsinyasi')
                                                ->label('Harga Satuan')
                                                ->numeric()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    // Recalculate subtotal when price changes
                                                    $jml = $get('jml_konsinyasi') ?? 1;
                                                    $set('subtotal_konsinyasi', ($state ?? 0) * $jml);
                                                    self::calculateGrandTotal($get, $set);
                                                })
                                                ->dehydrated(),
                                            
                                            TextInput::make('jml_konsinyasi')
                                                ->label('Jumlah')
                                                ->default(1)
                                                ->numeric()
                                                ->minValue(1)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    $stokTersedia = $get('stok_tersedia_konsinyasi') ?? 0;
                                                    if ($state > $stokTersedia) {
                                                        $set('jml_konsinyasi', $stokTersedia);
                                                        $state = $stokTersedia;
                                                    }
                                                    
                                                    // Calculate subtotal when quantity changes
                                                    $hargaJual = $get('harga_jual_konsinyasi') ?? 0;
                                                    $set('subtotal_konsinyasi', $hargaJual * $state);
                                                    
                                                    self::calculateGrandTotal($get, $set);
                                                }),
                                            
                                            TextInput::make('harga_beli_konsinyasi')
                                                ->label('Harga Beli')
                                                ->numeric()
                                                ->default(0)
                                                ->dehydrated(),
                                                
                                            TextInput::make('subtotal_konsinyasi')
                                                ->label('Subtotal')
                                                ->numeric()
                                                ->default(0)
                                                ->readonly()
                                                ->dehydrated(),
                                        ])
                                        ->columns(5)
                                        ->columnSpan(8),
                                ])
                                ->addable()
                                ->deletable()
                                ->reorderable()
                                ->createItemButtonLabel('Tambah Item')
                                ->minItems(1)
                                ->required()
                                ->collapsible()
                                ->columnSpanFull()
                                ->deleteAction(
                                    fn (Action $action, Get $get, Set $set) => $action->after(function () use ($get, $set) {
                                        self::calculateGrandTotal($get, $set);
                                    })
                                ),

                            Forms\Components\Section::make('Ringkasan')
                                ->schema([
                                    Placeholder::make('total_tagihan')
                                        ->label('Total Tagihan')
                                        ->content(function (Get $get) {
                                            $total = $get('tagihan') ?: 0;
                                            return 'Rp ' . number_format($total, 0, ',', '.');
                                        })
                                        ->columnSpan(2),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Aksi')
                                ->schema([
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('Simpan Sementara')
                                            ->action(function ($get, $set) {
                                                DB::transaction(function () use ($get, $set) {
                                                    // Calculate grand total first
                                                    $grandTotal = self::calculateGrandTotal($get, $set);
                                                    
                                                    // Create or update the penjualan record
                                                    $penjualan = Penjualan::updateOrCreate(
                                                        ['no_faktur' => $get('no_faktur')],
                                                        [
                                                            'pembeli_id' => $get('pembeli_id'),
                                                            'status' => 'pesan',
                                                            'tagihan' => $grandTotal, // Set calculated grand total
                                                            'tgl' => $get('tgl'),
                                                        ]
                                                    );

                                                    // Delete existing items
                                                    PenjualanBarang::where('penjualan_id', $penjualan->id)->delete();

                                                    foreach ($get('items') as $item) {
                                                        // Process regular items
                                                        if (!empty($item['Kode_barang'])) {
                                                            $barang = Barang::find($item['Kode_barang']);
                                                            if (!$barang || $barang->stok < ($item['jml'] ?? 0)) {
                                                                throw new \Exception('Stok barang tidak mencukupi');
                                                            }

                                                            $subtotal = ($item['harga_jual'] ?? 0) * ($item['jml'] ?? 0);
                                                            
                                                            $dataReguler = [
                                                                'penjualan_id' => $penjualan->id,
                                                                'tgl' => $item['tgl'] ?? now()->toDateString(),
                                                                'Kode_barang' => $item['Kode_barang'],
                                                                'harga_jual' => $item['harga_jual'],
                                                                'harga_beli' => $item['harga_beli'] ?? $barang->harga_beli ?? 0,
                                                                'jml' => $item['jml'],
                                                                'subtotal' => $subtotal,
                                                            ];

                                                            PenjualanBarang::create($dataReguler);
                                                            $barang->decrement('stok', $item['jml']);
                                                        }

                                                        // Process konsinyasi items
                                                        if (!empty($item['kode_barang_konsinyasi'])) {
                                                            $barangKonsinyasi = BarangKonsinyasi::find($item['kode_barang_konsinyasi']);
                                                            if (!$barangKonsinyasi || $barangKonsinyasi->stok < ($item['jml_konsinyasi'] ?? 0)) {
                                                                throw new \Exception('Stok barang konsinyasi tidak mencukupi');
                                                            }

                                                            $subtotalKonsinyasi = ($item['harga_jual_konsinyasi'] ?? 0) * ($item['jml_konsinyasi'] ?? 0);
                                                            
                                                            $dataKonsinyasi = [
                                                                'penjualan_id' => $penjualan->id,
                                                                'tgl' => $item['tgl'] ?? now()->toDateString(),
                                                                'Kode_barang' => null,
                                                                'harga_jual' => $item['harga_jual_konsinyasi'],
                                                                'harga_beli'=> $item['harga_beli_konsinyasi'] ?? $barangKonsinyasi->harga_beli ?? 0,
                                                                'jml' => $item['jml_konsinyasi'],
                                                                'kode_barang_konsinyasi' => $item['kode_barang_konsinyasi'],
                                                                'subtotal' => $subtotalKonsinyasi,
                                                            ];

                                                            PenjualanBarang::create($dataKonsinyasi);
                                                            $barangKonsinyasi->decrement('stok', $item['jml_konsinyasi']);
                                                        }
                                                    }

                                                    // Update penjualan's tagihan again to ensure it's saved
                                                    $penjualan->update(['tagihan' => $grandTotal]);
                                                    // Update form data
                                                    $set('tagihan', $grandTotal);
                                                    
                                                    Notification::make()
                                                        ->title('Pesanan berhasil disimpan')
                                                        ->success()
                                                        ->send();
                                                });
                                            })
                                            ->label('Proses')
                                            ->color('primary')
                                            ->size('lg')
                                            ->icon('heroicon-o-check'),
                                    ])
                                    ->columnSpan(2),
                                ])
                                ->columns(2),
                        ]),
                    Wizard\Step::make('Pembayaran')
                        ->schema([
                            // Display transaction information
                            Placeholder::make('info_pembayaran')
                                ->content(function (Get $get) {
                                    $penjualan = Penjualan::where('no_faktur', $get('no_faktur'))->first();
                                    if (!$penjualan) {
                                        return 'Tidak ada data transaksi';
                                    }
                                    
                                    $html = '<div class="p-4 bg-white rounded-xl border border-gray-200">';
                                    $html .= '<table class="w-full">
                                        <tr>
                                            <td class="py-2 font-medium">No Faktur</td>
                                            <td class="py-2">: ' . $penjualan->no_faktur . '</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Tanggal</td>
                                            <td class="py-2">: ' . $penjualan->created_at->format('Y-m-d H:i:s') . '</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Pembeli</td>
                                            <td class="py-2">: ' . ($penjualan->pembeli ? $penjualan->pembeli->nama_pembeli : '-') . '</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Total Tagihan</td>
                                            <td class="py-2 font-bold">: ' . rupiah($penjualan->tagihan) . '</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Status</td>
                                            <td class="py-2">: <span class="px-2 py-1 text-xs font-medium rounded-full ' . 
                                                ($penjualan->status == 'bayar' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') . 
                                                '">' . ucfirst($penjualan->status) . '</span></td>
                                        </tr>
                                    </table>';
                                    $html .= '</div>';
                                    
                                    // Show details of items
                                    $html .= '<div class="mt-4 p-4 bg-white rounded-xl border border-gray-200">';
                                    $html .= '<h3 class="text-lg font-medium mb-3">Detail Item</h3>';
                                    $html .= '<table class="w-full border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="p-2 text-left border">Item</th>
                                                <th class="p-2 text-right border">Harga</th>
                                                <th class="p-2 text-right border">Jumlah</th>
                                                <th class="p-2 text-right border">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                                    
                                    foreach ($penjualan->penjualanBarang as $item) {
                                        $namaBarang = '-';
                                        if ($item->Kode_barang) {
                                            $barang = Barang::find($item->Kode_barang);
                                            $namaBarang = $barang ? $barang->nama_barang : '-';
                                        } elseif ($item->kode_barang_konsinyasi) {
                                            $barangKonsinyasi = BarangKonsinyasi::find($item->kode_barang_konsinyasi);
                                            $namaBarang = $barangKonsinyasi ? $barangKonsinyasi->nama_barang . ' (Konsinyasi)' : '-';
                                        }
                                        
                                        $subtotal = $item->subtotal ?? ($item->harga_jual * $item->jml);
                                        
                                        $html .= '<tr>
                                            <td class="p-2 border">' . $namaBarang . '</td>
                                            <td class="p-2 text-right border">' . rupiah($item->harga_jual) . '</td>
                                            <td class="p-2 text-right border">' . $item->jml . '</td>
                                            <td class="p-2 text-right border">' . rupiah($subtotal) . '</td>
                                        </tr>';
                                    }
                                    
                                    $html .= '<tr class="bg-gray-50 font-bold">
                                        <td class="p-2 border" colspan="3">Total</td>
                                        <td class="p-2 text-right border">' . rupiah($penjualan->tagihan) . '</td>
                                    </tr>';
                                    
                                    $html .= '</tbody></table>';
                                    $html .= '</div>';
                                    
                                    return new \Illuminate\Support\HtmlString($html);
                                }),
                            
                            // Payment action if not yet paid
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('bayar')
                                    ->label('Proses Pembayaran')
                                    ->action(function (Get $get) {
                                        DB::transaction(function () use ($get) {
                                            $noFaktur = $get('no_faktur');
                                            $penjualan = Penjualan::where('no_faktur', $noFaktur)->first();
                                            
                                            if (!$penjualan) {
                                                throw new \Exception('Penjualan tidak ditemukan');
                                            }
                                            
                                            // Update penjualan status only
                                            $penjualan->update(['status' => 'bayar']);
                                            
                                            Notification::make()
                                                ->title('Pembayaran berhasil diproses')
                                                ->success()
                                                ->send();
                                        });
                                    })
                                    ->color('success')
                                    ->size('lg')
                                    ->icon('heroicon-o-banknotes')
                                    ->requiresConfirmation()
                                    ->modalHeading('Konfirmasi Pembayaran')
                                    ->modalDescription('Yakin ingin memproses pembayaran ini?')
                                    ->modalSubmitActionLabel('Ya, Proses Pembayaran')
                                    ->visible(function (Get $get) {
                                        $penjualan = Penjualan::where('no_faktur', $get('no_faktur'))->first();
                                        return $penjualan && $penjualan->status === 'pesan';
                                    })
                            ])
                            ->columnSpanFull(),
                        ]),
                ])->columnSpan(3)
            ]);
    }

    private static function calculateGrandTotal(Get $get, Set $set)
    {
        $grandTotal = 0;
        
        // Check if items exists and is iterable
        $items = $get('items');
        if (is_array($items) || is_object($items)) {
            foreach ($items as $item) {
                // For regular items
                if (!empty($item['Kode_barang']) && !empty($item['jml'])) {
                    $subtotal = ($item['harga_jual'] ?? 0) * ($item['jml'] ?? 0);
                    $grandTotal += $subtotal;
                }
                
                // For konsinyasi items
                if (!empty($item['kode_barang_konsinyasi']) && !empty($item['jml_konsinyasi'])) {
                    $subtotalKonsinyasi = ($item['harga_jual_konsinyasi'] ?? 0) * ($item['jml_konsinyasi'] ?? 0);
                    $grandTotal += $subtotalKonsinyasi;
                }
            }
        }
        
        $set('../../tagihan', $grandTotal);
        return $grandTotal;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_faktur')->label('No Faktur')->searchable(),
                TextColumn::make('pembeli.nama_pembeli')
                    ->label('Nama Pembeli')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bayar' => 'success',
                        'pesan' => 'warning',
                        default => 'secondary',
                    }),
                TextColumn::make('tagihan')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->sortable()
                    ->alignment('end'),
                TextColumn::make('created_at')->label('Tanggal')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pesan' => 'Pemesanan',
                        'bayar' => 'Pembayaran',
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}
