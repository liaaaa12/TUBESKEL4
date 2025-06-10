<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuBesarResource\Pages;
use App\Filament\Resources\BukuBesarResource\RelationManagers;
use App\Models\BukuBesar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// tambahan
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\Section;

class BukuBesarResource extends Resource
{
    protected static ?string $model = BukuBesar::class;
    // protected static string $view = 'filament.resources.buku-besar-resource.pages.index'; // Tentukan view kustom

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl')->date(),
                TextColumn::make('no_referensi')->label('Ref'),
                TextColumn::make('deskripsi')->limit(30),
                TextColumn::make('jurnaldetail.debit')
                    ->label('Total Debit')
                    ->formatStateUsing(function ($state, $record) {
                        // Menghitung jumlah debit dari relasi jurnaldetail
                        // dd(var_dump($record));  // Debugging untuk melihat data relasi
                        $debit = $record->jurnaldetail()->sum('debit'); 
                        return rupiah($debit);
                    })
                    ->alignment('end') // Rata kanan
                , 
                TextColumn::make('jurnaldetail.credit')
                    ->label('Total Kredit')
                    ->formatStateUsing(function ($state, $record) {
                        $credit = $record->jurnaldetail()->sum('credit'); 
                        return rupiah($credit);
                    })
                    ->alignment('end') // Rata kanan
                , 
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBukuBesars::route('/'),
            // 'create' => Pages\CreateBukuBesar::route('/create'),
            // 'edit' => Pages\EditBukuBesar::route('/{record}/edit'),
        ];
    }

    // Menampilkan data sesuai periode yang dipilih
    // public static function getViewData(): array
    // {
    //     // $periode = request('periode') ?? now()->format('Y-m'); // Periode default

    //     // $bukuBesarsQuery = BukuBesar::query();

    //     // if ($periode) {
    //     //     [$year, $month] = explode('-', $periode);
    //     //     $bukuBesarsQuery->whereYear('tgl', $year)->whereMonth('tgl', $month);
    //     // }

    //     // $bukuBesars = $bukuBesarsQuery->get();

    //     // return [
    //     //     'bukuBesars' => $bukuBesars,
    //     //     'periode' => $periode,
    //     // ];
    // }
}