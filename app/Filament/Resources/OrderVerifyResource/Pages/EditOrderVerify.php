<?php

namespace App\Filament\Resources\OrderVerifyResource\Pages;

use App\Filament\Resources\OrderVerifyResource;
use App\Models\OrderDetail;
use App\Models\OrderVerify;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrderVerify extends EditRecord
{
    protected static string $resource = OrderVerifyResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // $order = OrderVerify::find($data['id'])->with(['order'])->get()->first();
        // $data['order']['location'] =[
        //     'lng' => $order->order->location->longitude,
        //     'lat' =>  $order->order->location->latitude,
        // ];
        return $data;
    }
}
