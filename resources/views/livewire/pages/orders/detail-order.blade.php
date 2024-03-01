<div>
    <div class="grid grid-cols-3 gap-x-4">
        {{-- Bukti Bayar Produk --}}
        <div class="rounded-md">
            @if ($order->order_payment_method == 'transfer' && $order->order_payment_proof == null)
                <p class="text-lg font-medium">Bukit Bayar Belum Tersedia</p>
                <img class="w-full h-[400px] rounded-md" src="/storage/placeholders/no-photo-available.png"
                    alt="Foto Tidak Tersedia">
            @endif
            {{-- If COD Order --}}
            @if ($order->order_payment_method == 'cod')
                <p class="text-lg font-medium">Bukit Bayar Tidak Tersedia, Pemesanan via COD</p>
                <img class="w-full h-[400px] rounded-md" src="/storage/placeholders/no-photo-available.png"
                    alt="Foto Tidak Tersedia">
            @endif
            {{-- Payment Photo is not null --}}
            @if ($order->order_payment_method == 'transfer' && $order->order_payment_proof != null)
                <img class="w-full h-[400px] rounded-md" src="/storage/{{ $order->order_proof_payment }}"
                    alt="Foto Tidak Tersedia">
            @endif

        </div>
        {{-- Deskripsi Produk --}}
        <div class="flex flex-col">
            @foreach ($order->details as $detailOrders)
                <h2 class="text-2xl font-bold">{{ $detailOrders->product->product_name }}</h2>
                <div class="flex items-center text-sm font-medium gap-x-2">
                    <span>Jumlah Pembelian: {{ $detailOrders->order_product_stock }} Pcs | Tanggal Pesan:
                        {{ $order->order_date }}</span>
                </div>
                <div class="my-4 flex flex-col">
                    @if ($detailOrders->product->product_discount == 0)
                    <p class="text-xl font-bold">Rp. {{ $detailOrders->product->product_price }}/Pcs</p>
                    @else
                     {{-- If has discount --}}
                    <p class="text-xl font-bold">Rp. {{ $detailOrders->product->product_price }}/Pcs</p>
                    <div class="flex items-center gap-x-2">
                        <span class="text-sm text-slate-400 line-through">Rp.
                            {{ number_format($detailOrders->product->product_price * ($detailOrders->product->product_discount / 100), 0, ',', '.') }}</span>
                        <span
                            class="p-1 text-xs text-red-600 font-bold rounded-md bg-red-400">{{ $detailOrders->product->product_discount }}%</span>
                    </div>
                    @endif

                </div>
                <div class="flex flex-col">
                    <h3 class="text-xl font-bold">Deskripsi Produk</h3>
                    <p class="my-2 text-sm">{{ $detailOrders->product->product_description }}</p>
                </div>
            @endforeach
        </div>
        {{-- Review Produk --}}
        <div class="flex flex-col gap-y-2">
            <h2 class="text-2xl font-bold">Pembayaran</h2>
            <div class="flex flex-col gap-y-4">
                <div class="p-2 flex flex-col bg-neutral-100 rounded-md shadow-md">
                    <div class="flex items-center gap-x-2">
                        <h4 class="text-sm font-medium">Nama Pemesan:</h4>
                        <span class="my-2 text-sm">{{ $order->customer->customer_name }}</span>
                    </div>
                    <div class="flex items-center gap-x-2">
                        <h4 class="text-sm font-medium">Alamat Pengiriman:</h4>
                        <span class="my-2 text-sm">{{ $order->order_address }}</span>
                    </div>
                    <div class="flex items-center gap-x-2">
                        <h4 class="text-sm font-medium">Ongkir:</h4>
                        <span class="my-2 text-sm">Rp.
                            {{ number_format($order->order_deliver_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-x-2">
                        <h4 class="text-sm font-medium">Total Pembayaran:</h4>
                        <span class="my-2 text-sm">Rp. {{ number_format($order->order_charge, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-x-2">
                        <h4 class="text-sm font-medium">Status Pesanan:</h4>
                        @if ($order->order_status == 'wait')
                            <span class="my-2 text-sm">Menunggu Proses Verifikasi Pesanan</span>
                        @elseif ($order->order_status == 'accept')
                            <span class="my-2 text-sm">Pesanan Diterima dan Dalam Proses Pengiriman</span>
                        @else
                            <span class="my-2 text-sm">Pesanan Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
