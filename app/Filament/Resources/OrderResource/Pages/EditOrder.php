<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderDetail;
use App\Models\OrderVerify;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $orderDetails = OrderDetail::where('order_id', '=', $data['id'])->get();
        $products = [];
        foreach ($orderDetails as $order) {
            $input = [
                'product_id' => $order->product_id,
                'order_product_stock' => $order->order_product_stock
            ];
            array_push($products, $input);
        }

        $data['products'] = $products;
        $data['location'] = [
            'lng' => $data['location']['coordinates'][0],
            'lat' =>  $data['location']['coordinates'][1]
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $orderStatus = $data['order_status'];
        $orderId = $record->id;
        try {
            $orderVerify = OrderVerify::findOrFail($orderId);
            if ($orderStatus === 'accept') {
                $orderVerify->shipment_status = 'proses';
                $orderVerify->save();
            } else {
                $orderVerify->delete();
            }
            $record->order_status = $orderStatus;
            $record->update($data);
        } catch (ModelNotFoundException $ex) {
            if ($orderStatus === 'accept') {
                OrderVerify::create([
                    'order_id' => $orderId
                ]);
            }
            $record->order_status = $orderStatus;
            $record->update($data);
        }
        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
