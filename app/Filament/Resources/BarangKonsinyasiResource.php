<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangKonsinyasiResource\Pages;
use App\Models\BarangKonsinyasi;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

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
                Forms\Components\TextInput::make('kode_barang_konsinyasi')
                    ->label('Kode Barang Konsinyasi')
                    ->disabled() // Agar tidak bisa diedit manual
                    ->dehydrated(false), // Tidak dikirimkan saat submit form

                Forms\Components\TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),

                Forms\Components\TextInput::make('pemilik')
                    ->label('Pemilik')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kode_barang_konsinyasi')
                    ->label('Kode Barang Konsinyasi')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->sortable()
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('pemilik')
                    ->label('Pemilik')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
