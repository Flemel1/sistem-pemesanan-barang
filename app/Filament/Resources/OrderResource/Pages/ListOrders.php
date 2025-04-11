<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Laporan')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tangga Awal')
                        ->required(),
                    DatePicker::make('end_date')
                        ->required()
                ])
                ->action(function (array $data): void {
                    $startDate = $data['start_date'];
                    $endDate = $data['end_date'];

                    redirect()->route('generate-report', ['start_date' => $startDate, 'end_date' => $endDate]);
                })
                ->modalSubmitActionLabel('Download'),
        ];
    }
}
