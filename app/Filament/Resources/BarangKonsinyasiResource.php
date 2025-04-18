<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangKonsinyasiResource\Pages;
use App\Models\BarangKonsinyasi;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class BarangKonsinyasiResource extends Resource
{
    protected static ?string $model = BarangKonsinyasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Barang Konsinyasi';
    protected static ?string $slug = 'barang-konsinyasi';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('kode_barang_konsinyasi')
                    ->label('Kode Barang Konsinyasi')
                    ->default(fn () => BarangKonsinyasi::getKodeBarangKonsinyasi())
                    ->required()
                    ->readonly(),
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->required(),
                FileUpload::make('foto')
                    ->label('Foto')
                    ->directory('foto')
                    ->image()
                    ->required(),
                TextInput::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->required(),
                TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->required(),
                TextInput::make('pemilik')
                    ->label('Pemilik')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('kode_barang_konsinyasi')->label('Kode Barang'),
                TextColumn::make('nama_barang')->label('Nama Barang')->sortable(),
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->size(40),
                TextColumn::make('stok')->label('Stok')->sortable(),
                TextColumn::make('harga')->label('Harga')->sortable(),
                TextColumn::make('pemilik')->label('Pemilik')->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangKonsinyasis::route('/'),
            'create' => Pages\CreateBarangKonsinyasi::route('/create'),
            'edit' => Pages\EditBarangKonsinyasi::route('/{record}/edit'),
        ];
    }
}
