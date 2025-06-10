<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Route;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\InputMask;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Kode_barang')
                    ->default(fn() => Barang::getKodeBarang()) // Ambil default dari method getKodeBarang
                    ->label('Id Barang')
                    ->required()
                    ->readonly() // Membuat field menjadi read-only
                ,
                TextInput::make('nama_barang')
                    ->required()
                    ->placeholder('Masukkan nama barang') // Placeholder untuk membantu pengguna
                ,
                TextInput::make('harga_barang')
                    ->required()
                    ->reactive()
                    ->extraAttributes(['id' => 'harga-barang'])
                    ->placeholder('Masukkan harga barang')
                    ->live()
                    ->afterStateUpdated(
                        fn($state, callable $set) =>
                        $set('harga_barang', number_format((int) str_replace('.', '', $state), 0, ',', '.'))
                    )
                    ->dehydrateStateUsing(
                        fn($state) =>
                        (int) str_replace('.', '', $state) // Ubah kembali ke angka sebelum disimpan
                    )
                    ->numeric(),
                FileUpload::make('foto')
                    ->directory('foto')
                    ->visibility('public'),
                TextInput::make('stok')
                    ->required()
                    ->placeholder('Masukkan stok barang') // Placeholder untuk membantu pengguna
                    ->minValue(0),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Kode_barang')
                    ->searchable(),
                // agar bisa di search
                TextColumn::make('nama_barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harga_barang')
                    ->label('Harga Barang')
                    ->formatStateUsing(fn($state) => rupiah($state))

                    ->extraAttributes(['class' => 'text-right']) // Tambahkan kelas CSS untuk rata kanan
                    ->sortable(),
                ImageColumn::make('foto'),
                TextColumn::make('stok'),

            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
