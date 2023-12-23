<?php

namespace App\Filament\Customer\Resources\ProductResource\Pages;

use App\Filament\Customer\Resources\ProductResource;
use App\Models\Order;
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
        $record = null;
        try {
            DB::beginTransaction();
            $product = Product::find($data['product_id']);
            $product_stock = $product->product_stock;
            $product_stock_purchase = $data['order_product_stock'];
            if ($product_stock < $product_stock_purchase) {
                DB::rollBack();
                $this->getCreatedNotification('Pesanan gagal dibuat', 'Stok yang ingin dibeli tidak mencukupi', false)->send();
                $this->halt();
            }
            $product->product_stock = $product_stock - $product_stock_purchase;
            $product->save();
            $location = new Point($data['location']['lat'], $data['location']['lng']);
            $data['location'] = $location;
            $record = new Order($data);
            $record->save();
            DB::commit();
            return $record;
        } catch (Exception $ex) {
            DB::rollBack();
            dd($ex);
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

        $product = Product::find($data['product_id']);
        $distance = GeoFacade::setPoint([-7.773580, 110.380803])
            ->setOptions(['units' => ['km']])
            ->setPoint([$data['location']['lat'], $data['location']['lng']])
            ->getDistance()['1-2']['km'];
        $data['customer_id'] = auth()->id();
        if ($distance > 1) {
            $data['order_deliver_fee'] = 1500 * $distance;
        } else {
            $data['order_deliver_fee'] = 1500;
        }
        $discount = $product->product_discount;
        if ($discount == 0) {
            $data['order_charge'] = $product->product_price * $data['order_product_stock'];
            $data['order_charge'] += $data['order_deliver_fee'];
        } else {
            $data['order_charge'] = ($product->product_price - (($discount / 100) * $product->product_price)) * $data['order_product_stock'];
            $data['order_charge'] += $data['order_deliver_fee'];
        }

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
