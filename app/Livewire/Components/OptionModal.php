<?php

namespace App\Livewire\Components;

use App\Livewire\Pages\CustomerHome;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\On;
use Livewire\Component;

class OptionModal extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public array $categoryFilter = [];
    public string $sort = '';

    public function optionAction(): Action
    {
        return Action::make('option')
            ->label('Filter')
            ->fillForm([
                'category_filter' => $this->categoryFilter,
                'price_order' => $this->sort
            ])
            ->form([
                CheckboxList::make('category_filter')
                    ->label('Filter Kategori')
                    ->options(Category::all()->pluck('category_name', 'id'))
                    ->columns(4),
                Radio::make('price_order')
                    ->label('Urutan Harga')
                    ->options([
                        'lower' => 'Berdasarkan Harga Terendah',
                        'higher' => 'Berdasarkan Harga Tertinggi'
                    ])
                    ->columns(2)
            ])
            ->action(function (array $data) {
                $this->categoryFilter = $data['category_filter'];
                $categories = $data['category_filter'];
                $sort = $data['price_order'];
                if ($sort) {
                    $this->sort = $sort;
                }
                $this->dispatch('search-empty')->to(ProductSearch::class);
                $this->dispatch('filter-product', categories: $categories)->to(CustomerHome::class);
                $this->dispatch('sort-products', sort: $this->sort)->to(CustomerHome::class);
            });
    }

    #[On('filter-empty')]
    public function filterEmpty(): void
    {
        $this->categoryFilter = [];
        $this->sort = '';
    }

    public function render()
    {
        return view('livewire.components.option-modal');
    }
}
