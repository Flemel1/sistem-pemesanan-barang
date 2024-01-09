<?php

namespace App\Filament\Customer\Resources\ProductResource\Pages;

use App\Filament\Customer\Resources\ProductResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use MatanYadaev\EloquentSpatial\Objects\Point;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Buat Pesanan';

    public ?Model $record = null;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $order = null;
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
            return $order;
        } catch (Exception $ex) {
            DB::rollBack();
            // dd($ex);
            $this->halt();
        }

        //return $record;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $location = Location::get();
        // if ($location) {
        //     $product = Product::find($data['product_id']);
        //     $data['customer_id'] = auth()->id();
        //     $data['order_deliver_fee'] = 5000;
        //     $discount = $product->product_discount;
        //     if ($discount == 0) {
        //         $data['order_charge'] = $product->product_price * $data['order_product_stock'];
        //     } else {
        //         $data['order_charge'] = ($product->product_price - (($discount / 100) * $product->product_price)) * $data['order_product_stock'];
        //     }
        // } else {
        //     dd('hello');
        //     $this->halt();
        // }
        $data['order_charge'] = 0;
        $distance = GeoFacade::setPoint([-7.773580, 110.380803])
            ->setOptions(['units' => ['km']])
            ->setPoint([$data['location']['lat'], $data['location']['lng']])
            ->getDistance()['1-2']['km'];
        $customer = Customer::where('user_id', auth()->id())->first();
        $data['customer_id'] = $customer->id;
        if ($distance > 1) {
            $data['order_deliver_fee'] = 1500 * $distance;
        } else {
            $data['order_deliver_fee'] = 1500;
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(?string $title = 'Pesanan berhasil dibuat'): ?string
    {
        if ($title == null) {
            return 'Pesanan berhasil dibuat';
        }
        return $title;
    }

    protected function getCreatedNotification(
        string $title = null,
        string $body = 'Silakan tunggu proses selanjutnya',
        bool $is_success = true
    ): ?Notification {
        if (!$is_success) {
            return Notification::make()
                ->danger()
                ->title($this->getCreatedNotificationTitle($title))
                ->body($body);
        }

        return Notification::make()
            ->success()
            ->title($this->getCreatedNotificationTitle($title))
            ->body($body);
    }
}
