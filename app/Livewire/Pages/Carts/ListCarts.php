<?php

namespace App\Livewire\Pages\Carts;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Setting;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Exception;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ListCarts extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Cart::query();
                $customer = Customer::where('user_id', '=', auth()->id())->first();
                $query->where('customer_id', $customer->id);
                return $query;
            })
            ->columns([
                TextColumn::make('product.product_name')
                    ->label('Nama Produk'),
                TextColumn::make('cart_product_stock')
                    ->label('Jumlah (Pcs)')
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('checkout')
                        ->icon('heroicon-m-archive-box')
                        ->color('success')
                        ->fillForm(function (Collection $records): array {
                            $data = [
                                'products' => []
                            ];
                            foreach ($records as $product) {
                                array_push($data['products'], [
                                    'product_id' => $product->product_id,
                                    'order_product_stock' => $product->cart_product_stock
                                ]);
                            }
                            return $data;
                        })
                        ->steps([
                            Step::make('order')
                                ->label('Pesanan')
                                ->schema([
                                    Repeater::make('products')
                                        ->schema([
                                            Select::make('product_id')
                                                ->label('Pilih Produk')
                                                ->options(Product::all()->pluck('product_name', 'id'))
                                                ->required()
                                                ->distinct()
                                                ->searchable()
                                                ->disabled(),
                                            TextInput::make('order_product_stock')
                                                ->label('Jumlah Produk Dibeli')
                                                ->required()
                                                ->numeric(),
                                        ])
                                        ->addable(false)
                                        ->deletable(false)
                                        ->defaultItems(1)
                                        ->reorderable(false)
                                        ->addActionLabel('Tambah Produk')
                                        ->minItems(1)
                                        ->columns(2)
                                        ->columnSpanFull(),
                                    Textarea::make('order_address')
                                        ->label('Alamat Pengiriman')
                                        ->required()
                                        ->rows(5)
                                        ->columnSpanFull(),
                                    Select::make('order_payment_method')
                                        ->options(['cod' => 'COD', 'transfer' => 'Transfer'])
                                        ->required(),
                                    Map::make('location')
                                        ->defaultLocation([-7.7860101, 110.3787211])
                                        ->clickable()
                                        ->defaultZoom(15)
                                        ->geolocate()
                                        ->geolocateLabel('Lokasi Saat Ini')
                                        ->required()
                                ]),
                            Step::make('Tagihan')
                                ->schema(fn (Get $get) => $this->getDetailOrder($get))
                                ->columns(4)
                        ])
                        ->action(function (array $data, $livewire, Collection $records) {
                            try {
                                DB::beginTransaction();
                                $data['order_charge'] = 0;
                                $customer = Customer::where('user_id', auth()->id())->first();
                                $data['customer_id'] = $customer->id;
                                $setting = Setting::find(1)->first();
                                foreach ($records as $cart) {
                                    $currentProduct = Product::find($cart['product_id']);
                                    $product_stock = $currentProduct->product_stock;
                                    $product_stock_purchase = $cart['cart_product_stock'];
                                    if ($product_stock < $product_stock_purchase) {
                                        DB::rollBack();
                                        $this->getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                                        $this->halt();
                                    }
                                    $currentProduct->product_stock = $product_stock - $product_stock_purchase;
                                    $discount = $currentProduct->product_discount;
                                    if ($discount == 0) {
                                        $data['order_charge'] += $currentProduct->product_price * $cart['cart_product_stock'];
                                    } else {
                                        $data['order_charge'] += ($currentProduct->product_price - (($discount / 100) * $currentProduct->product_price)) * $data['order_product_stock'];
                                    }
                                    $currentProduct->save();
                                }
                                $distance = GeoFacade::setPoint([$setting->location->latitude, $setting->location->longitude])
                                    ->setOptions(['units' => ['km']])
                                    ->setPoint([$data['location']['lat'], $data['location']['lng']])
                                    ->getDistance()['1-2']['km'];
                                if ($distance > 1) {
                                    $data['order_deliver_fee'] = $setting->order_deliver_fee * $distance;
                                } else {
                                    $data['order_deliver_fee'] = $setting->order_deliver_fee;
                                }
                                $data['order_charge'] += $data['order_deliver_fee'];
                                $location = new Point($data['location']['lat'], $data['location']['lng']);
                                $data['location'] = $location;
                                $order = new Order($data);
                                $order->save();
                                $orderId = $order->id;
                                foreach ($records as $cart) {
                                    $input = [
                                        'order_id' => $orderId,
                                        'product_id' => $cart['product_id'],
                                        'order_product_stock' => $cart['cart_product_stock']
                                    ];
                                    $orderDetail = new OrderDetail($input);
                                    $orderDetail->save();
                                    $cart->delete();
                                }
                                DB::commit();
                                self::getCreatedNotification()->send();
                            } catch (Exception $ex) {
                                DB::rollBack();
                                self::getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make('delete-cart-products')
                        ->label('Hapus Produk'),
                ])
                    ->label('Pilihan'),
            ]);
    }

    private function getDetailOrder(Get $get): array
    {
        $numberOrder = 1;
        $components = [];
        $products = $get('products');
        foreach ($products as $index => $product) {
            if ($product['product_id'] != null) {
                $price = 0;
                $currentProduct = Product::find($product['product_id']);
                $discount = $currentProduct->product_discount;
                if ($discount == 0) {
                    $price += $currentProduct->product_price * $product['order_product_stock'];
                } else {
                    $price += ($currentProduct->product_price - (($discount / 100) * $currentProduct->product_price)) * $product['order_product_stock'];
                }
                $section_products = Section::make('Produk')
                    ->schema(
                        [
                            Placeholder::make('product_name_' . $index)
                                ->label("No")
                                ->content(fn (): string => $numberOrder),
                            Placeholder::make('product_name_' . $index)
                                ->label("Produk")
                                ->content(fn (): string => $currentProduct->product_name),
                            Placeholder::make('product_stock_' . $index)
                                ->label("Jumlah Dibeli")
                                ->content(fn (): string => $product['order_product_stock']),
                            Placeholder::make('product_price_' . $index)
                                ->label("Harga/pcs")
                                ->content(fn (): string => 'Rp. ' . number_format($price, thousands_separator: '.'))
                        ]
                    )
                    ->columns(4);
                $components[] = $section_products;
            }
            $numberOrder++;
        }
        [$total_products, $ongkir, $total_ongkir_products] = $this->getFees($get);
        $bill_section = Section::make('Total Tagihan/Pembayaran')
            ->schema([
                Placeholder::make('total_products')
                    ->label('Total barang')
                    ->inlineLabel()
                    ->content(fn (): string => 'Rp. ' . $total_products),
                Placeholder::make('ongkir')
                    ->label('Ongkir')
                    ->inlineLabel()
                    ->content(fn (): string => 'Rp. ' . $ongkir),
                Placeholder::make('bill')
                    ->label('Total yang Dibayar')
                    ->inlineLabel()
                    ->content(fn (): string => 'Rp. ' . $total_ongkir_products)
            ])
            ->columns(1);
        $components[] = $bill_section;

        return $components;
    }

    public function getFees(Get $get): array
    {
        $deliveryFee = 0;
        $total = 0;
        $ongkir = 0;
        $location = $get('location');
        $products = $get('products');
        if ($location && $products) {
            $setting = Setting::find(1)->first();
            $distance = GeoFacade::setPoint([$setting->location->latitude, $setting->location->longitude])
                ->setOptions(['units' => ['km']])
                ->setPoint([$location['lat'], $location['lng']])
                ->getDistance()['1-2']['km'];
            if ($distance > 1) {
                $deliveryFee = $setting->order_deliver_fee * $distance;
            } else {
                $deliveryFee = $setting->order_deliver_fee;
            }

            $ongkir = $deliveryFee;

            foreach ($products as $product) {
                if ($product['product_id'] != null) {
                    $currentProduct = Product::find($product['product_id']);
                    $discount = $currentProduct->product_discount;
                    if ($discount == 0) {
                        $deliveryFee += $currentProduct->product_price * $product['order_product_stock'];
                        $total += $currentProduct->product_price * $product['order_product_stock'];
                    } else {
                        $deliveryFee += ($currentProduct->product_price - (($discount / 100) * $currentProduct->product_price)) * $product['order_product_stock'];
                        $total += ($currentProduct->product_price - (($discount / 100) * $currentProduct->product_price)) * $product['order_product_stock'];
                    }
                }
            }

            return [number_format($total, thousands_separator: '.'), number_format($ongkir, thousands_separator: '.'), number_format($deliveryFee, thousands_separator: '.')];
        }
        return [number_format($total, thousands_separator: '.'), number_format($ongkir, thousands_separator: '.'), number_format($deliveryFee, thousands_separator: '.')];
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

    public function render(): View
    {
        return view('livewire.pages.carts.list-carts');
    }
}
