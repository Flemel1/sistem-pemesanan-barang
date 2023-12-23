<?php

namespace App\Filament\Customer\Resources\CartResource\Pages;

use App\Filament\Customer\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;
}
