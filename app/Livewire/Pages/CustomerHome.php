<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomerHome extends Component
{
    public Collection $products;

    public function mount()
    {
        $this->products = Product::with(['reviews'])->get();
    }

    #[On('search-product')]
    public function updateProductList(string $productName)
    {
        $productsFilter = Product::where('product_name', 'LIKE', "%$productName%")->get();
        $this->products = $productsFilter;
    }

    #[On('filter-product')]
    public function updateProductListByCategories(array $categories)
    {
        $products = [];
        $sizeOfCategory = sizeof($categories);
        if ($sizeOfCategory == 0) {
            $products = Product::all();
        }
        if ($sizeOfCategory == 1) {
            $productsFilter = Product::where('category_id', '=', $categories[0]);

            $products = $productsFilter->get();
        } else if ($sizeOfCategory > 1) {
            $productsFilter = Product::where('category_id', '=', $categories[0]);
            for ($i = 1; $i < $sizeOfCategory; $i++) {
                $productsFilter = $productsFilter->orWhere('category_id', '=', $categories[$i]);
            }

            $products = $productsFilter->get();
        }

        $this->products = $products;
    }

    #[On('sort-products')]
    public function sortProducts(string $sort)
    {
        if ($sort === 'lower') {
            $this->products = $this->products->sortBy('product_price');
        } else {
            $this->products = $this->products->sortByDesc('product_price');
        }
    }

    public function render()
    {
        return view('livewire.pages.customer-home');
    }
}
