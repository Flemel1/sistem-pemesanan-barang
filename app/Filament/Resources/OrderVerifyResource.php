<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderVerifyResource\Pages;
use App\Models\OrderVerify;
use App\Models\Product;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderVerifyResource extends Resource
{
    protected static ?string $model = OrderVerify::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pesanan Berhasil';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('order')
                    ->label('Pesanan')
                    ->relationship('order')
                    ->schema([
                        Repeater::make('products')
                            ->relationship('details')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Pilih Produk')
                                    ->options(Product::all()->pluck('product_name', 'id'))
                                    ->required()
                                    ->distinct()
                                    ->searchable(),
                                TextInput::make('order_product_stock')
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
                        Textarea::make('order_address')
                            ->label('Alamat Pengiriman')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->disabled(),
                        Select::make('order_payment_method')
                            ->options(['cod' => 'COD', 'transfer' => 'Transfer'])
                            ->required()
                            ->disabled(),
                        Select::make('order_status')
                            ->options([
                                'wait' => 'Proses Verfikasi',
                                'accept' => 'Pesanan Diterima',
                                'reject' => 'Pesanan Ditolak'
                            ])
                            ->disabled(),
                        TextInput::make('order_charge')
                            ->label('Jumlah yang Dibayar (Rupiah)')
                            ->required()
                            ->numeric()
                            ->disabled(),
                        FileUpload::make('order_proof_payment')
                            ->directory('payments')
                            ->visibility('private')
                            ->disabled(),
                        // Map::make('location')
                        //     ->clickable()
                        //     ->defaultZoom(15)
                        //     ->geolocate()
                        //     ->geolocateLabel('Lokasi Saat Ini')
                        //     ->disabled()
                        //     ->columnSpanFull()
                    ]),
                Select::make('shipment_status')
                    ->options([
                        'proses' => 'Pesanan Diproses',
                        'kemas' => 'Pesanan Dikemas',
                        'kirim' => 'Pesanan Dikirim'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')->label('ID Pesanan'),
                TextColumn::make('order.customer.customer_name')->label('Nama Pemesan'),
                TextColumn::make('order.order_status')
                    ->label('Status Pesanan')
                    ->formatStateUsing(function (string $state) {
                        if ($state == 'wait') {
                            return 'Menunggu Proses Verifikasi Pesanan';
                        } else if ($state == 'accept') {
                            return 'Pesanan Diterima';
                        } else {
                            return 'Pesanan Dibatalkan';
                        }
                    }),
                TextColumn::make('shipment_status')->label('Status Barang'),
                TextColumn::make('order.order_date')->label('Tanggal Pesan'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make('edit-order-verify')
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
            'index' => Pages\ListOrderVerifies::route('/'),
            // 'create' => Pages\CreateOrderVerify::route('/create'),
            'edit' => Pages\EditOrderVerify::route('/{record}/edit'),
        ];
    }
}
