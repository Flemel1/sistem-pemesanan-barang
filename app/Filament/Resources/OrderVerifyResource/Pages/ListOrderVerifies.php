<?php

namespace App\Filament\Resources\OrderVerifyResource\Pages;

use App\Filament\Resources\OrderVerifyResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListOrderVerifies extends ListRecords
{
    protected static string $resource = OrderVerifyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->form([
                    DatePicker::make('start_date')
                        ->required(),
                    DatePicker::make('end_date')
                        ->required()
                ])
                ->action(function (array $data): void {
                    $startDate = $data['start_date'];
                    $endDate = $data['end_date'];

                    redirect()->route('generate-report-verify', ['start_date' => $startDate, 'end_date' => $endDate]);
                })
                ->modalSubmitActionLabel('Download'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Daftar Pesanan Berhasil';
    }
}
