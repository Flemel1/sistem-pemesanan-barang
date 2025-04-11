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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $shipmentStatus = $data['shipment_status'];
        $orderId = $record->id;
        if ($shipmentStatus === 'kirim') {
            $record->finished_at = now();
            $record->update($data);
        } else {
            $record->finished_at = null;
            $record->update($data);
        }
        return $record;
    }
}
