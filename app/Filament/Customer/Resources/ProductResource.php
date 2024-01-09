<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\ProductResource\Pages;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Infolists;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('order_address')
                    ->label('Alamat Pengiriman')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Forms\Components\Select::make('order_payment_method')
                    ->options(['cod' => 'COD', 'transfer' => 'Transfer'])
                    ->required(),
                Map::make('location')
                    ->defaultLocation([-7.7860101, 110.3787211])
                    ->clickable()
                    ->defaultZoom(15)
                    ->geolocate()
                    ->geolocateLabel('Lokasi Saat Ini')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product_photo')
                    ->label('Foto')
                    ->height(200),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_stock')->label('Stok')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('save-to-chart')
                    ->label('Keranjang')
                    ->form([
                        Forms\Components\TextInput::make('cart_product_stock')
                            ->label('Jumlah Produk Dibeli')
                            ->required()
                            ->numeric(),
                    ])
                    ->action(function (array $data, Product $product): void {
                        $customer = Customer::where('user_id', '=', auth()->id())->first();
                        $data['customer_id'] = $customer->id;
                        $data['product_id'] = $product->id;
                        $record = new Cart($data);
                        $record->save();
                    })

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoLists\Components\TextEntry::make('product_name')->label('Nama Produk'),
                InfoLists\Components\TextEntry::make('product_stock')->label('Jumlah Stok'),
                InfoLists\Components\TextEntry::make('product_description')
                    ->label('Deskripsi Produk'),
                InfoLists\Components\TextEntry::make('product_price')
                    ->label('Harga Produk')
                    ->money('IDR'),
                InfoLists\Components\RepeatableEntry::make('reviews')
                    ->schema([
                        InfoLists\Components\TextEntry::make('customer.customer_name')->label('Nama Pengguna')->columns(2),
                        InfoLists\Components\TextEntry::make('review_rating')->label('Rating'),
                        InfoLists\Components\TextEntry::make('review_text')->label('Ulasan')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->grid(2)
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
