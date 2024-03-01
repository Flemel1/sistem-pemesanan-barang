<?php

namespace App\Filament\Resources\OrderVerifyResource\Pages;

use App\Filament\Resources\OrderVerifyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListOrderVerifies extends ListRecords
{
    protected static string $resource = OrderVerifyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Daftar Pesanan Berhasil';
    }
}
