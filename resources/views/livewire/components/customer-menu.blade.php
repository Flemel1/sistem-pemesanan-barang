@php
    $user = filament()->auth()->user();
    $items = [];

    $profileItem = $items['profile'] ?? $items['account'] ?? null;
    $profileItemUrl = $profileItem?->getUrl();
    $profilePage = filament()->getProfilePage();
    $hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

    $logoutItem = $items['logout'] ?? null;

    $items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook('panels::user-menu.before') }}

<x-filament::dropdown
    placement="bottom-end"
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0"
        >
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::user-menu.profile.before') }}

        @if ($hasProfileItem)
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    :color="$profileItem?->getColor()"
                    :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
                    :href="$profileItemUrl ?? filament()->getProfileUrl()"
                    :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null"
                    tag="a"
                >
                    {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user) }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        @else
            <x-filament::dropdown.header
                :color="$profileItem?->getColor()"
                :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            >
                {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
            </x-filament::dropdown.header>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook('panels::user-menu.profile.after') }}
    @endif

    {{-- @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif --}}

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
            <x-filament::dropdown.list.item
                :color="$item->getColor()"
                :href="$item->getUrl()"
                :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null"
                :icon="$item->getIcon()"
                tag="a"
            >
                {{ $item->getLabel() }}
            </x-filament::dropdown.list.item>
        @endforeach
        <x-filament::dropdown.list.item
            :href="route('customer.orders')"
            :color="null"
            :icon="'heroicon-m-document-check'"
            tag="a"
        >
            Order
        </x-filament::dropdown.list.item>
        <x-filament::dropdown.list.item
            :action="route('logout')"
            :color="$logoutItem?->getColor()"
            :icon="$logoutItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ?? 'heroicon-m-arrow-left-on-rectangle'"
            method="post"
            tag="form"
        >
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>

        
    </x-filament::dropdown.list>
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook('panels::user-menu.after') }}
