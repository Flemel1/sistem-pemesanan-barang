<?php

namespace App\Filament\Customer\Resources\ProductResource\Pages;

use App\Filament\Customer\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create-order')
                ->url(fn(): string => $this->getResource()::getUrl('create'))
                ->label('Pesan Barang')
        ];
    }
}
