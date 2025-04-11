<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pengiriman</title>
    <style>
        h1 {
            font-weight: 700;
            font-size: 1.875rem;
            line-height: 2.25rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .date {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }

        .divider {
            border: 1px black solid;
        }

        .group-date {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        table {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            border-collapse: collapse;
            width: 100%;
        }

        table td,
        table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
        }

        table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #000;
            color: white;
        }

        .text-left {
            text-align: left;
        }

        .border {
            border-width: 1px;
        }

        ol {
            padding-left: 0.8rem;
        }
    </style>
</head>

<body>
    <h1>Laporan Pemesanan Berhasil</h1>
    <div class="group-date">
        <span class="date">Tanggal: {{ $date }}</span>
        <div class="divider"></div>
    </div>
    <table class='mt-4 table w-full'>
        <thead class="table-header-group">
            <tr class="text-left border">
                <th class="table-cell">ID Pemesanan</th>
                <th>Nama Pemesan</th>
                <th class="table-cell">Status Pengiriman</th>
                <th class="table-cell">Tanggal Order Masuk</th>
                <th class="table-cell">Nama Produk</th>
                <th class="table-cell">Jumlah Produk</th>
                <th class="table-cell">Keterangan</th>
                <th class="table-cell">Total Bayar</th>
            </tr>
        </thead>
        <tbody class="table-row-group">
            @foreach ($data as $item)
            
                <tr class="table-row border">
                    <td class="table-cell">{{ $item['id'] }}</td>
                    <td class="table-cell">{{ $item['name'] }}</td>
                    <td class="table-cell">{{ $item['status'] }}</td>
                    <td class="table-cell">{{ $item['date'] }}</td>
                    <td class="table-cell">
                        <ol>
                            @foreach ($item['product_name'] as $name)
                                <li>{{ $name }}</li>
                            @endforeach
                        </ol>

                    </td>
                    <td class="table-cell">
                        <ol>
                            @foreach ($item['order_count'] as $count)
                                <li>{{ $count }} Pcs</li>
                            @endforeach
                        </ol>

                    </td>
                    <td class="table-cell">{{ $item['description'] }}</td>
                    <td class="table-cell">Rp. {{ number_format($item['charge'], 0, ',', '.') }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="text-align: right;">Total Pendapatan: Rp. {{ number_format($total, 0, ',', '.') }}</p>
</body>

</html>
