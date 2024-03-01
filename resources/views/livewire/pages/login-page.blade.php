<x-filament-panels::page.simple>

    <x-slot name="subheading">
        {{ $this->registerAction }}
    </x-slot>

    <x-filament-panels::form wire:submit="login">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
