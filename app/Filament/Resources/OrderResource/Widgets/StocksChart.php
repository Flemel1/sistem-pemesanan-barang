<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class StocksChart extends ChartWidget
{
    protected static ?string $heading = 'Produk Terlaris';

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $trend = DB::table('top_5_best_products')->get();
        return [
            'datasets' => [
                [
                    'label' => 'Total Terjual',
                    'data' => $trend->map(fn (object $value) => $value->total_sell),
                ],
            ],
            'labels' => $trend->map(fn (object $value) => $value->product_name),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
