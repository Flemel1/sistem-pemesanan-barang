@props(['product'])

<div>
    {{ $this->cartAction(['product' => $product->id]) }}
 
    <x-filament-actions::modals />
</div>
