<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\CartResource\Pages;
use App\Filament\Customer\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use MatanYadaev\EloquentSpatial\Objects\Point;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Columns\TextColumn::make('product.product_name')
                    ->label('Nama Produk'),
                Tables\Columns\TextColumn::make('cart_product_stock')
                    ->label('Jumlah (Pcs)')
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Action::make('create-order')
                    ->label('Checkout')
                    ->fillForm(function (Cart $record): array {
                        return [
                            'product_id' => $record->product_id,
                            'order_product_stock' => $record->cart_product_stock
                        ];
                    })
                    ->form([
                        Forms\Components\Select::make('product_id')
                            ->label('Pilih Produk')
                            ->options(Product::all()->pluck('product_name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('order_product_stock')
                            ->label('Jumlah Produk Dibeli')
                            ->required()
                            ->numeric(),
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
                    ])
                    ->action(function (array $data, Cart $cart): void {
                        try {
                            DB::beginTransaction();
                            $product = Product::find($data['product_id']);
                            $customer = Customer::where('user_id', auth()->id())->first();
                            $data['customer_id'] = $customer->id;
                            $distance = GeoFacade::setPoint([-7.773580, 110.380803])
                                ->setOptions(['units' => ['km']])
                                ->setPoint([$data['location']['lat'], $data['location']['lng']])
                                ->getDistance()['1-2']['km'];
                            if ($distance > 1) {
                                $data['order_deliver_fee'] = 1500 * $distance;
                            } else {
                                $data['order_deliver_fee'] = 1500;
                            }
                            $discount = $product->product_discount;
                            if ($discount == 0) {
                                $data['order_charge'] = $product->product_price * $data['order_product_stock'];
                            } else {
                                $data['order_charge'] = ($product->product_price - (($discount / 100) * $product->product_price)) * $data['order_product_stock'];
                            }
                            $product_stock = $product->product_stock;
                            $product_stock_purchase = $data['order_product_stock'];
                            if ($product_stock < $product_stock_purchase) {
                                DB::rollBack();
                                self::getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                                return;
                            }
                            $product->product_stock = $product_stock - $product_stock_purchase;
                            $product->save();
                            $location = new Point($data['location']['lat'], $data['location']['lng']);
                            $data['location'] = $location;
                            $order = new Order($data);
                            $order->save();
                            $orderId = $order->id;
                            $input = [
                                'order_id' => $orderId,
                                'product_id' => $data['product_id'],
                                'order_product_stock' => $data['order_product_stock']
                            ];
                            $orderDetail = new OrderDetail($input);
                            $orderDetail->save();
                            $cart->delete();
                            DB::commit();
                            self::getCreatedNotification()->send();
                        } catch (Exception $ex) {
                            DB::rollBack();
                            self::getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'view' => Pages\ViewCart::route('/{record}'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $customer = Customer::where('user_id', '=', auth()->id())->first();
        return parent::getEloquentQuery()->where('customer_id', $customer->id)->withoutGlobalScopes();
    }

    public static function getCreatedNotificationTitle(?string $title = 'Pesanan berhasil dibuat'): ?string
    {
        if ($title == null) {
            return 'Pesanan berhasil dibuat';
        }
        return $title;
    }

    public static function getCreatedNotification(
        string $title = null,
        string $body = 'Silakan tunggu proses selanjutnya',
        bool $is_success = true
    ): ?Notification {
        if (!$is_success) {
            return Notification::make()
                ->danger()
                ->title(self::getCreatedNotificationTitle($title))
                ->body($body);
        }

        return Notification::make()
            ->success()
            ->title(self::getCreatedNotificationTitle($title))
            ->body($body);
    }
}
