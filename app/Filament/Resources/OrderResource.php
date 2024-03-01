<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('products')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Pilih Produk')
                            ->options(Product::all()->pluck('product_name', 'id'))
                            ->required()
                            ->distinct()
                            ->searchable(),
                        Forms\Components\TextInput::make('order_product_stock')
                            ->label('Jumlah Produk Dibeli')
                            ->required()
                            ->numeric(),
                    ])
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->addActionLabel('Tambah Produk')
                    ->minItems(1)
                    ->columns(2)
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('order_address')
                    ->label('Alamat Pengiriman')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Select::make('order_payment_method')
                    ->options(['cod' => 'COD', 'transfer' => 'Transfer'])
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('order_status')
                    ->options([
                        'wait' => 'Proses Verfikasi',
                        'accept' => 'Pesanan Diterima',
                        'reject' => 'Pesanan Ditolak'
                    ]),
                Forms\Components\TextInput::make('order_charge')
                    ->label('Jumlah yang Dibayar (Rupiah)')
                    ->required()
                    ->numeric()
                    ->disabled(),
                Forms\Components\FileUpload::make('order_proof_payment')
                    ->directory('payments')
                    ->visibility('private')
                    ->disabled(),
                Map::make('location')
                    ->clickable()
                    ->defaultZoom(15)
                    ->geolocate()
                    ->geolocateLabel('Lokasi Saat Ini')
                    ->disabled()
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID Pesanan'),
                Tables\Columns\TextColumn::make('customer.customer_name')->label('Nama Pemesan'),
                // Tables\Columns\TextColumn::make('product.product_name')->label('Nama Produk'),
                // Tables\Columns\TextColumn::make('order_product_stock')->label('Jumlah (Pcs)')
                //     ->numeric(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status Pesanan')
                    ->formatStateUsing(function (string $state) {
                        if ($state == 'wait') {
                            return 'Menunggu Proses Verifikasi Pesanan';
                        } else if ($state == 'accept') {
                            return 'Pesanan Diterima dan Dalam Proses Pengiriman';
                        } else {
                            return 'Pesanan Dibatalkan';
                        }
                    }),
                Tables\Columns\TextColumn::make('order_date')->label('Tanggal Pesan'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
