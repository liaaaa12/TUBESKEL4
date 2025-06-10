<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput; //kita menggunakan textinput
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\CoaResource\Pages;
use App\Filament\Resources\CoaResource\RelationManagers;
use App\Models\Coa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoaResource extends Resource
{
    protected static ?string $model = Coa::class;
    protected static ?string $navigationLabel = 'Coa';
    protected static ?string $slug = 'Coa';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1) // Membuat hanya 1 kolom
                ->schema([
                    TextInput::make('header_akun')
                        ->required()
                        ->placeholder('Masukkan header akun')
                    ,
                    TextInput::make('kode_akun')
                        ->required()
                        ->placeholder('Masukkan kode akun')
                    ,
                    TextInput::make('nama_akun')
                        ->autocapitalize('words')
                        ->label('Nama akun')
                        ->required()
                        ->placeholder('Masukkan nama akun')
                    ,
                    TextInput::make('saldo')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp ')
                        ->label('Saldo')
                        ->required()
                    ,
                    Forms\Components\Select::make('posisi')
                        ->options([
                            'debit' => 'Debit',
                            'kredit' => 'Kredit',
                        ])
                        ->default('debit')
                        ->required()
                        ->label('Posisi')
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('header_akun')
                    ->label('Header Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kode_akun')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_akun')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('saldo')
                    ->formatStateUsing(fn ($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->label('Saldo')
                    ->sortable(),
                TextColumn::make('posisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit' => 'success',
                        'kredit' => 'danger',
                    })
                    ->label('Posisi')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('header_akun')
                ->options([
                    11 => 'Aktiva Lancar',
                    21 => 'Utang Usaha',
                    31 => 'Modal',
                    41 => 'Pendapatan',
                    51 => 'Beban',
                ]),
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
            'index' => Pages\ListCoas::route('/'),
            'create' => Pages\CreateCoa::route('/create'),
            'edit' => Pages\EditCoa::route('/{record}/edit'),
        ];
    }
}
