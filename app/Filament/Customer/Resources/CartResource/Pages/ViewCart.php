<?php

namespace App\Filament\Customer\Resources\CartResource\Pages;

use App\Filament\Customer\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
