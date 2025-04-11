<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Order;
use App\Models\Shipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $currentOrders = DB::table('orders as o')
            ->select(DB::raw('o.id as id, o.created_at as date, o.order_status as order_status, s.shipment_status as shipment_status'))
            ->join('shipment as s', 'o.id', '=', 's.order_id', type: 'left')
            ->whereDate('o.created_at', today())
            ->where('o.order_status', 'NOT LIKE', DB::raw('\'reject\''))->get();
        $currentShipment = Shipment::whereDate('created_at', today())->get();
        $unFinishOrders = DB::table('orders as o')
            ->select(DB::raw('o.id as id, DATE(o.created_at) as date, o.order_status as order_status, s.shipment_status as shipment_status'))
            ->join('shipment as s', 'o.id', '=', 's.order_id', type: 'left')
            ->where(function($query) {
                $query->where('o.order_status', '=', DB::raw('\'accept\''))
                    ->where('s.shipment_status', '!=', 'null')
                    ->where('s.shipment_status', '!=', DB::raw('\'kirim\''));
            })->orWhere('o.order_status', '=', DB::raw('\'wait\''))->get();
        $countFinishCurrentProduct = $currentShipment->where('shipment_status', 'kirim')->count();
        $countUnfinishCurrentProduct = $unFinishOrders->where('date', '=', today()->format('Y-m-d'))->count();
        $countCurrentOrders = $currentOrders->count();
        $countUnfinishProduct = $unFinishOrders->count();
        
        return [
            Stat::make('Total Order Hari Ini', $countCurrentOrders),
            Stat::make('Order Selesai Hari Ini', $countFinishCurrentProduct),
            Stat::make('Order Tidak Selesai Hari Ini', $countUnfinishCurrentProduct),
            Stat::make('Total Order Tidak Selesai', $countUnfinishProduct),
        ];
    }
}
