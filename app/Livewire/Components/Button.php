<?php

namespace App\Livewire\Components;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Setting;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\DB;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use Livewire\Component;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Button extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Product $product;
    

    public function buyAction(): Action
    {
        return Action::make('buy')
            ->label('Beli')
            ->color('success')
            ->size(ActionSize::Large)
            ->fillForm([
                'products' => [
                    0 => [
                        "product_id" => $this->product->id,
                        "order_product_stock" => 1
                    ]
                ]
            ])
            ->steps([
                Step::make('Pesan')
                    ->schema([
                        Repeater::make('products')
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
                    ->schema([
                        Placeholder::make('bill')
                            ->content(fn (): string => 'Test')
                    ])
            ])
            ->action(function (array $data) {
                // $data = $this->mutateFormDataBeforeCreate($data);
                dd($data);
                // $this->create($data);
            });
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order_charge'] = 0;
        $setting = Setting::find(1)->first();
        $distance = GeoFacade::setPoint([$setting->location->latitude, $setting->location->longitude])
            ->setOptions(['units' => ['km']])
            ->setPoint([$data['location']['lat'], $data['location']['lng']])
            ->getDistance()['1-2']['km'];
        $customer = Customer::where('user_id', auth()->id())->first();
        $data['customer_id'] = $customer->id;
        if ($distance > 1) {
            $data['order_deliver_fee'] = $setting->order_deliver_fee * $distance;
        } else {
            $data['order_deliver_fee'] = $setting->order_deliver_fee;
        }
        $products = $data['products'];
        foreach ($products as $product) {
            $currentProduct = Product::find($product['product_id']);
            $discount = $currentProduct->product_discount;
            if ($discount == 0) {
                $data['order_charge'] += $currentProduct->product_price * $product['order_product_stock'];
            } else {
                $data['order_charge'] += ($currentProduct->product_price - (($discount / 100) * $currentProduct->product_price)) * $data['order_product_stock'];
            }
        }
        $data['order_charge'] += $data['order_deliver_fee'];
        return $data;
    }

    public function create(array $data): void
    {
        try {
            DB::beginTransaction();
            $products = $data['products'];
            foreach ($products as $product) {
                $currentProduct = Product::find($product['product_id']);
                $product_stock = $currentProduct->product_stock;
                $product_stock_purchase = $product['order_product_stock'];
                if ($product_stock < $product_stock_purchase) {
                    DB::rollBack();
                    $this->getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                    $this->halt();
                }
                $currentProduct->product_stock = $product_stock - $product_stock_purchase;
                $currentProduct->save();
            }
            $location = new Point($data['location']['lat'], $data['location']['lng']);
            $data['location'] = $location;
            $order = new Order($data);
            $order->save();
            $orderId = $order->id;
            foreach ($products as $product) {
                $input = [
                    'order_id' => $orderId,
                    'product_id' => $product['product_id'],
                    'order_product_stock' => $product['order_product_stock']
                ];
                $orderDetail = new OrderDetail($input);
                $orderDetail->save();
            }
            DB::commit();
            $this->createNotification();
        } catch (Exception $ex) {
            DB::rollBack();
            // dd($ex);
            $this->createNotification(false);
        }
    }

    public function createNotification(bool $isSuccess = true)
    {
        if ($isSuccess) {
            Notification::make()
                ->title('Pesanan Telah Dibuat')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Pesanan Gagal Dibuat')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.components.button');
    }
}
