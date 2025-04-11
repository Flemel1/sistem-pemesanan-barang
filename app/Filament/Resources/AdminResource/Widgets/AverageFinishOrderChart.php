<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Shipment;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class AverageFinishOrderChart extends ChartWidget
{
    protected static ?string $heading = 'Waktu Rerata Produk Selesai (1 Minggu Terakhir)';

    protected function getData(): array
    {
        $period = CarbonPeriod::create(now()->subWeek(), now());
        $lastWeekAverageFinishOrders = [];
        $tempDiffMinutes = [];
        $minutes = [];
        $dates = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $dates[] = $formattedDate;
            $finishOrders = Shipment::whereDate('created_at', $formattedDate)
                ->where('shipment_status', 'kirim')
                ->get();
            if (sizeof($finishOrders) == 0) {
                $tempDiffMinutes[] = 0;
            } else {
                foreach ($finishOrders as $order) {
                    $created_at = $order->created_at;
                    $finished_at = $order->finished_at;
                    $interval =  $created_at->diff($finished_at);
                    $diffMinutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
                    $tempDiffMinutes[] = $diffMinutes;
                }
            }
            $minutes = collect($tempDiffMinutes);
            $average = $minutes->avg();
            $lastWeekAverageFinishOrders[] = $average;
            $tempDiffMinutes = [];
        }
        return [
            'datasets' => [
                [
                    'label' => 'Menit',
                    'data' => $lastWeekAverageFinishOrders,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
