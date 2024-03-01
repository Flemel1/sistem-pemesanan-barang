<div class="mb-4 flex flex-col">
    <h2 class="text-xl font-bold">Produk Populer</h2>
    <div class="relative my-4 flex gap-4">
        <div class="my-4 flex gap-4 overflow-hidden">
            @foreach ($samples as $sample)
                <a href="/home" style="transform: translateX({{ $translateX }}%);" wire:navigate>
                    <div @class([
                        'w-[230px] h-[250px] mb-4 flex flex-col shadow-md rounded-lg',
                    ])>
                        <img @class(['w-full h-1/2 rounded-t-lg']) src="storage/products/01HP4N3MEKN4XBKC37NPMY82B4.png"
                            alt="Produk">
                        <div @class(['p-2 flex flex-col gap-1'])>
                            <p class="font-bold line-clamp-2 text-base">Headset - Earphone - Handsfree PINZY MS001 Bass
                                Sound
                            </p>
                            <span class="text-sm">Rp. {{ number_format(200000, 0, ',', '.') }}</span>
                            <div class="flex gap-2">
                                <span class="fa fa-star text-yellow-500"></span>
                                <span class="text-xs">5.0 | {{ $sample }} Tersisa</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div @class([
            'w-[32px] h-[32px] absolute right-[-15px] top-[50%] mt-[-22px] pl-[11px] pt-[3px] text-base text-white rounded-full bg-slate-700 cursor-pointer',
            'hidden' => !$isNext,
        ]) wire:click="next">
            &#10095;
        </div>
        <div @class([
            'w-[32px] h-[32px] absolute left-[-15px] top-[50%] mt-[-22px] pl-[11px] pt-[3px] text-base text-white rounded-full bg-slate-700 cursor-pointer',
            'hidden' => !$isPrev,
        ]) wire:click="prev">
            &#10094;
        </div>
    </div>
</div>
