<?php

namespace App\Filament\Customer\Resources\HistoryResource\Pages;

use App\Filament\Customer\Resources\HistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHistory extends ViewRecord
{
    protected static string $resource = HistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
