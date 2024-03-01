@props(['navigation'])

<div {{ $attributes->class(['fi-topbar sticky top-0 z-20 overflow-x-clip']) }}>
    <nav
        class="flex h-16 items-center gap-x-4 bg-white px-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8">
        <div x-persist="topbar.end" class="w-full flex justify-between items-center gap-x-4">

            <a href="{{ route('customer.index') }}">
                <x-filament-panels::logo />
            </a>

            @if (Route::is('customer.index'))
                <livewire:components.product-search />
            @endif
            <div class="flex gap-4">
                <a href="{{ route('customer.carts') }}" wire:navigate>
                    <i class="fa fa-shopping-cart fa-lg"></i>
                </a>
                <a href="/chat">Chat</a>
                @if (filament()->auth()->check())
                    @if (filament()->hasDatabaseNotifications())
                        @livewire(Filament\Livewire\DatabaseNotifications::class, ['lazy' => true])
                    @endif

                    <livewire:components.customer-menu />
                @endif
            </div>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::topbar.end') }}
    </nav>
</div>
