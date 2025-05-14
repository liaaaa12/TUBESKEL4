<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianBarangResource\Pages;
use App\Filament\Resources\PembelianBarangResource\RelationManagers;
use App\Models\PembelianBarang;
use App\Models\VendorBarang;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ExportAction;
use App\Filament\Exports\PembelianBarangExporter;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PembelianBarangResource extends Resource
{
    protected static ?string $model = PembelianBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pembelian Barang';
    protected static ?string $slug = 'pembelian-barang';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_barang_id')
                    ->label('Vendor Barang')
                    ->options(VendorBarang::all()->pluck('nama_vndr_brg', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('barang_id')
                    ->label('Barang')
                    ->options(Barang::all()->pluck('nama_barang', 'id'))
                    ->required()
                    ->searchable(),
                    Forms\Components\TextInput::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $set('total', (int) $state * (int) $get('harga'));
                    }),
                Forms\Components\TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $set('total', (int) $get('stok') * (int) $state);
                    }),                
                Forms\Components\TextInput::make('total')
                    ->label('Total')
                    ->prefix('Rp')
                    ->readOnly()
                    ->reactive()
                    ->afterStateUpdated(function (
                        $state, $set, $get
                    ) {
                        $set('total', (int) $get('stok') * (int) $get('harga'));
                    }),
                Forms\Components\Select::make('keterangan')
                    ->label('Keterangan')
                    ->options([
                        'lunas' => 'Lunas',
                        'belum lunas' => 'Belum Lunas',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendorBarang.nama_vndr_brg')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->badge()
                    ->color(fn($state) => $state === 'lunas' ? 'success' : 'warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pembelian')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(PembelianBarangExporter::class),
                // Tombol Unduh PDF
                Action::make('downloadPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $data = \App\Models\PembelianBarang::with(['vendorBarang', 'barang'])->get()->map(function($item) {
                            return [
                                'vendor' => $item->vendorBarang->nama_vndr_brg ?? '-',
                                'barang' => $item->barang->nama_barang ?? '-',
                                'stok' => $item->stok,
                                'harga' => $item->harga,
                                'total' => $item->total,
                                'tanggal' => $item->created_at ? $item->created_at->format('d M Y H:i') : '-',
                            ];
                        });
                        $grandTotal = $data->sum('total');
                        $pdf = Pdf::loadView('pdf.contoh', ['data' => $data, 'grandTotal' => $grandTotal]);
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'pembelian-barang.pdf'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(PembelianBarangExporter::class),
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
            'index' => Pages\ListPembelianBarangs::route('/'),
            'create' => Pages\CreatePembelianBarang::route('/create'),
            'edit' => Pages\EditPembelianBarang::route('/{record}/edit'),
        ];
    }
}
