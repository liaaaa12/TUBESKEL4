<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KonsignorResource\Pages;
use App\Models\Konsignor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;


class KonsignorResource extends Resource
{
    protected static ?string $model = Konsignor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Konsignors';
    protected static ?string $pluralLabel = 'Konsignors';
    protected static ?string $modelLabel = 'Konsignor';
    // tambahan buat grup masterdata
    protected static ?string $navigationGroup = 'Masterdata';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id_konsignors')
                    ->default(fn () => Konsignor::getIdKonsignors())
                    ->label('ID Konsignor')
                    ->required()
                    ->readonly(),

                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('email')
                ->label('Nama')
                ->required()
                ->maxLength(255),

                Textarea::make('alamat')
                    ->label('Alamat')
                    ->required(),

                TextInput::make('no_telp')
                    ->label('No Telepon')
                    ->required()
                    ->maxLength(15)
                    ->regex('/^\+?[0-9]{10,15}$/')
                    ->helperText('Masukkan nomor telepon yang valid (10-15 digit).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_konsignors')
                    ->label('ID Konsignor')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                    TextColumn::make('email')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(50),

                TextColumn::make('no_telp')
                    ->label('No Telepon')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi di sini jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKonsignors::route('/'),
            'create' => Pages\CreateKonsignor::route('/create'),
            'edit' => Pages\EditKonsignor::route('/{record}/edit'),
        ];
    }
}
