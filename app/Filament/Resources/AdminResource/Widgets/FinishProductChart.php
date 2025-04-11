<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Shipment;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FinishProductChart extends ChartWidget
{
    protected static ?string $heading = 'Rekap Order';

    protected function getData(): array
    {

        $period = CarbonPeriod::create(now()->subWeek(), now());
        $lastWeekFinishOrders = [];
        $lastWeekTotalOrder = [];
        $dates = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $dates[] = $formattedDate;
            $data = DB::table('orders as o')
                ->select(DB::raw('SUM(IF(o.order_status != \'reject\',1,0)) as total_order, SUM(IF(s.shipment_status = \'kirim\',1,0)) as total_order_finish, o.created_at as date'))
                ->join('shipment as s', 'o.id', '=', 's.order_id', type: 'left')
                ->whereDate('o.created_at',  $formattedDate)
                ->get();
            // $count = Shipment::whereDate('created_at', $formattedDate)
            //     ->where('shipment_status', 'kirim')
            //     ->count();
            $lastWeekFinishOrders[] = $data[0]->total_order_finish == null ? 0 : $data[0]->total_order_finish;
            $lastWeekTotalOrder[] = $data[0]->total_order == null ? 0 : $data[0]->total_order;
        }

        return [
            'datasets' => [
                [
                    'type' => 'line',
                    'label' => 'Order Selesai',
                    'data' => $lastWeekFinishOrders,
                    'borderColor' => 'rgb(54, 162, 235)',
                ],
                [
                    'type' => 'bar',
                    'label' => 'Total Order',
                    'data' => $lastWeekTotalOrder,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return '';
    }
}
