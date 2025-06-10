<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorBarangResource\Pages\ListVendorBarang;
use App\Filament\Resources\VendorBarangResource\Pages\CreateVendorBarang;
use App\Filament\Resources\VendorBarangResource\Pages\EditVendorBarang;
use App\Filament\Resources\VendorBarangResource\RelationManagers;
use App\Models\VendorBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorBarangResource extends Resource
{
    protected static ?string $model = VendorBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Vendor Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_vendor_barang')
                    ->label('Kode Vendor')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(function () {
                        $latestVendor = VendorBarang::latest()->first();
                        if (!$latestVendor) {
                            return 'VB001';
                        }
                        $currentNumber = intval(substr($latestVendor->kode_vendor_barang, 2));
                        $nextNumber = $currentNumber + 1;
                        return 'VB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                    }),
                Forms\Components\TextInput::make('nama_vndr_brg')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Vendor'),
                Forms\Components\TextInput::make('alamat_vndr_brg')
                    ->required()
                    ->maxLength(100)
                    ->label('Alamat'),
                Forms\Components\TextInput::make('no_telp_vndr_brg')
                    ->required()
                    ->maxLength(255)
                    ->label('No. Telepon'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                Tables\Columns\TextColumn::make('kode_vendor_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Vendor'),
                Tables\Columns\TextColumn::make('nama_vndr_brg')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Vendor'),
                Tables\Columns\TextColumn::make('alamat_vndr_brg')
                    ->searchable()
                    ->sortable()
                    ->label('Alamat'),
                Tables\Columns\TextColumn::make('no_telp_vndr_brg')
                    ->searchable()
                    ->label('No. Telepon'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorBarang::route('/'),
            'create' => CreateVendorBarang::route('/create'),
            'edit' => EditVendorBarang::route('/{record}/edit'),
        ];
    }
}
