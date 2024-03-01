<div>
    <div class="grid grid-cols-3 gap-x-4">
        {{-- Gambar Produk --}}
        <div class="rounded-md">
            <img class="w-full h-[400px] rounded-md" src="/storage/{{ $product->product_photo }}" alt="Produk">
        </div>
        {{-- Deskripsi Produk --}}
        <div class="flex flex-col">
            <h2 class="text-2xl font-bold">{{ $product->product_name }}</h2>
            <div class="flex items-center text-base font-medium gap-x-2">
                <span>Tersisa {{ $product->product_stock }} |</span>
                <span class="fa fa-star text-yellow-500"></span>
                <span class="text-sm">
                    @if ($product->reviews->avg('review_rating'))
                        {{ $product->reviews->avg('review_rating') }}
                    @else
                        0
                    @endif
                </span>
            </div>
            <div class="my-4 flex flex-col">
                @if ($product->product_discount == 0)
                    <p class="text-xl font-bold">Rp. {{ number_format($product->product_price, 0, ',', '.') }}/{{ $product->product_unit }}</p>
                @else
                    {{-- If has discount --}}
                    <p class="text-xl font-bold">Rp.
                        {{ number_format($product->product_price * ($product->product_discount / 100), 0, ',', '.') }}/Pcs
                    </p>
                    <div class="flex items-center gap-x-2">
                        <span class="text-sm text-slate-400 line-through">Rp.
                            {{ number_format($product->product_price, 0, ',', '.') }}</span>
                        <span
                            class="p-1 text-xs text-red-600 font-bold rounded-md bg-red-400">{{ $product->product_discount }}%</span>
                    </div>
                @endif
            </div>
            <div class="flex flex-col">
                <h3 class="text-xl font-bold">Deskripsi Produk</h3>
                <p class="my-2 text-sm">{{ $product->product_description }}</p>
            </div>
            <div class="w-full flex gap-x-2">
                <livewire:components.button :product="$product" />
                <livewire:components.cart-button :product="$product" />
                {{-- <button type="button" class="w-1/4 p-2 text-white bg-emerald-400 rounded-md">Beli</button> --}}
                {{-- <button type="button" class="w-1/4 p-2 text-white bg-orange-400 rounded-md">Keranjang</button> --}}
                
            </div>
        </div>
        {{-- Review Produk --}}
        <div class="flex flex-col gap-y-2">
            <h2 class="text-2xl font-bold">Ulasan Produk</h2>
            <div class="flex flex-col gap-y-4">
                @foreach ($product->reviews as $review)
                    <div class="p-2 flex flex-col bg-neutral-100 rounded-md shadow-md">
                        <h4 class="text-base font-medium">{{ $review->customer->customer_name }}</h4>
                        <span class="fa fa-star text-sm text-yellow-500"><span
                                class="ml-1 text-black font-medium">{{ $review->review_rating }}</span></span>
                        <p class="my-2 text-sm">{{ $review->review_text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
