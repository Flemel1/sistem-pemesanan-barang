<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jimmyjs\ReportGenerator\Facades\PdfReportFacade;

class ReportGenerator extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $title = 'Laporan Pemesanan';
        $meta = [
            'Tanggal' => $startDate . ' Sampai ' . $endDate,
        ];
        $report = DB::table('orders as order')
            ->select(DB::raw('order.id as id,
                    customer.customer_name as name,
                    order.order_status as status,
                    order.order_date as date,
                    order.order_charge as charge'))
            ->join('customers as customer', 'customer.id', '=', 'order.customer_id')
            ->whereBetween(DB::raw('DATE(order.order_date)'), [$startDate, $endDate]);

        $columns = [
            'ID Pesanan' => 'id',
            'Nama Pemesan' => 'name',
            'Status Pesanan' => 'status',
            'Tanggal Pesanan' => 'date',
            'Total Bayar' => 'charge'
        ];

        return PdfReportFacade::of($title, $meta, $report, $columns)
            ->editColumn('Tanggal Pesanan', [
                'displayAs' => function ($data) {
                    $date = new DateTime($data->date);
                    return $date->format('d-M-Y');
                }
            ])
            ->editColumn('Status Pesanan', [
                'displayAs' => function ($data) {
                    if ($data->status == 'wait') {
                        return 'Menunggu Proses Verifikasi Pesanan';
                    } else if ($data->status == 'accept') {
                        return 'Pesanan Diterima';
                    } else {
                        return 'Pesanan Dibatalkan';
                    }
                }
            ])
            ->editColumn('Total Bayar', [
                'displayAs' => function ($data) {
                    return 'Rp. ' . number_format($data->charge, 0, ',', '.');
                }
            ])
            ->editColumn('Total Bayar', [
                'class' => 'right bold'
            ])
            ->showTotal([
                'Total Bayar' => 'point'
            ])
            ->download('laporan-pemesanan-' . $startDate);
    }

    public function report_verify(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $title = 'Laporan Pemesanan Berhasil';
        $meta = [
            'Tanggal' => $startDate . ' Sampai ' . $endDate,
        ];

        $titleDate = $startDate . ' Sampai ' . $endDate;
        $queryBuilder = DB::table('orders as order')
            ->select(DB::raw('order.id as id,
                    customer.customer_name as name,
                    p.product_name as product_name,
                    d.order_product_stock as order_count,
                    s.shipment_status as status,
                    order.order_date as date,
                    \'dijual online\' as description,
                    order.order_charge as charge'))
            ->join('order_details as d', 'd.order_id', '=', 'order.id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->join('customers as customer', 'customer.id', '=', 'order.customer_id')
            ->join('shipment as s', 's.order_id', '=', 'order.id')
            ->whereBetween(DB::raw('DATE(order.order_date)'), [$startDate, $endDate]);

        $data = $queryBuilder->get();

        $arr = [];
        $total_charge = 0;
        $arr_index = 0;
        $current_id_order = 0;

        foreach ($data as $item) {
            if ($current_id_order === 0) {
                $date = new DateTime($item->date);
                $current_id_order = $item->id;
                $arr[] = [
                    'id' => $current_id_order,
                    'name' => $item->name,
                    'status' => $item->status,
                    'date' => $date->format('d-M-Y'),
                    'description' => $item->description,
                    'charge' => $item->charge,
                    'product_name' => [$item->product_name],
                    'order_count' => [$item->order_count]
                ];
                $total_charge = $total_charge + $item->charge;
            } else {
                if ($current_id_order === $item->id) {
                    $arr[$arr_index]['product_name'][] = $item->product_name;
                    $arr[$arr_index]['order_count'][] = $item->order_count;
                } else {
                    $current_id_order = $item->id;
                    $arr_index = $arr_index + 1;
                    $date = new DateTime($item->date);
                    $arr[] = [
                        'id' => $current_id_order,
                        'name' => $item->name,
                        'status' => $item->status,
                        'date' => $date->format('d-M-Y'),
                        'description' => $item->description,
                        'charge' => $item->charge,
                        'product_name' => [$item->product_name],
                        'order_count' => [$item->order_count]
                    ];
                    $total_charge = $total_charge + $item->charge;
                }
            }
        }

        return Pdf::loadView('reports.order-verify-report', [
            'data' => $arr,
            'total' => $total_charge,
            'date' => $titleDate
        ])->download("laporan-pengiriman-$startDate.pdf");
        // $report = DB::table('orders as order')
        //     ->select(DB::raw('order.id as id,
        //             customer.customer_name as name,
        //             p.product_name as product_name,
        //             d.order_product_stock as order_count,
        //             s.shipment_status as status,
        //             order.order_date as date,
        //             \'dijual online\' as description,
        //             order.order_charge as charge'))
        //     ->join('order_details as d', 'd.order_id', '=', 'order.id')
        //     ->join('products as p', 'p.id', '=', 'd.product_id')
        //     ->join('customers as customer', 'customer.id', '=', 'order.customer_id')
        //     ->join('shipment as s', 's.order_id', '=', 'order.id')
        //     ->whereBetween(DB::raw('DATE(order.order_date)'), [$startDate, $endDate]);

        // $columns = [
        //     'ID Pesanan' => 'id',
        //     'Nama Pemesan' => 'name',
        //     'Status Pengiriman' => 'status',
        //     'Tanggal Order Masuk' => 'date',
        //     'Nama Produk' => 'product_name',
        //     'Jumlah Terjual' => 'order_count',
        //     'Keterangan' => 'description',
        //     'Total Bayar' => 'charge'
        // ];

        // return PdfReportFacade::of($title, $meta, $report, $columns)
        //     ->editColumn('Tanggal Order Masuk', [
        //         'displayAs' => function($data) {
        //             $date = new DateTime($data->date);
        //             return $date->format('d-M-Y');
        //         }
        //     ])
        //     ->editColumn('Total Bayar', [
        //         'displayAs' => function($data) {
        //             return 'Rp. ' . number_format($data->charge, 0, ',', '.');
        //         }
        //     ])
        //     ->editColumn('Total Bayar', [
        //         'class' => 'right bold'
        //     ])
        //     ->groupBy('ID Pesanan')
        //     ->showTotal([
        //         'Total Bayar' => 'point'
        //     ])
        //     ->download('laporan-pengiriman-' . $startDate);
    }
}
