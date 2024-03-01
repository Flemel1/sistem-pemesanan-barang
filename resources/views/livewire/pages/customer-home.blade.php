@php
    $samples = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
@endphp

<div>
    {{-- <livewire:components.popular-product /> --}}
    <h2 class="text-xl font-bold">Daftar Produk</h2>
    <div class="mt-4 flex">
        <div class="ms-auto">
            <livewire:components.option-modal />
        </div>
    </div>
    <div @class(['my-4 grid grid-cols-5 gap-4'])>
        {{-- @foreach ($products as $product)
            <div @class(['h-[250px] flex flex-col shadow-md'])>
                <img @class(['w-full h-1/2']) src="storage/{{ $product->product_photo }}" alt="Produk">
                <div @class(['p-2 flex flex-col gap-1'])>
                    <p class="font-bold truncate text-base">{{ $product->product_name }}</p>
                    <span class="text-sm">Rp. {{ number_format($product->product_price, 0, ',', '.') }}</span>
                    <div class="flex gap-2">
                        <span class="fa fa-star text-yellow-500"></span>
                        <span class="text-xs">5.0</span>
                    </div>
                </div>

            </div>
        @endforeach --}}

        @foreach ($products as $product)
            <a href="{{ route('customer.products.detail', ['product' => $product->id]) }}">
                <div @class(['h-[250px] flex flex-col shadow-md rounded-lg'])>
                    <img @class(['w-full h-1/2 rounded-t-lg']) src="/storage/{{ $product->product_photo }}" alt="Produk">
                    <div @class(['p-2 flex flex-col gap-1'])>
                        <p class="font-bold line-clamp-2 text-base">{{ $product->product_name }}
                        </p>
                        <span class="text-sm">Rp. {{ number_format($product->product_price, 0, ',', '.') }}</span>
                        <div class="flex gap-2">
                            <span class="fa fa-star text-yellow-500"></span>
                            <span class="text-xs">
                                @if ($product->reviews->avg('review_rating'))
                                    {{ $product->reviews->avg('review_rating') }}
                                @else
                                    0
                                @endif | {{ $product->product_stock }}
                                Tersisa</span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
