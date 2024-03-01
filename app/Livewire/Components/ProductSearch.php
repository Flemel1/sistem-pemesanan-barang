<?php

namespace App\Livewire\Components;

use App\Livewire\Pages\CustomerHome;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductSearch extends Component
{

    public string $search = '';

    public function updated($stateName): void
    {
        if ($stateName === 'search') {
            $this->dispatch('filter-empty')->to(OptionModal::class);
            $this->dispatch('search-product', productName: $this->search)->to(CustomerHome::class);
        }
    }

    #[On('search-empty')]
    public function emptySearch(): void
    {
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.components.product-search');
    }
}
