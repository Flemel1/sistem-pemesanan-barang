<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class RevenuesChart extends ChartWidget
{
    protected static ?string $heading = 'Hasil Penjualan';

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $trend = Trend::query(Order::where('order_status', '=', 'accept'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear()
            )
            ->perMonth()
            ->sum('order_charge');
        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $trend->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $trend->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
