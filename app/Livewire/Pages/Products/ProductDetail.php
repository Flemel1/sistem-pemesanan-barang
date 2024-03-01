<?php

namespace App\Livewire\Pages\Products;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = Product::findOrFail($product->id)->with('reviews')->get()->firstWhere('id', $product->id);
    }

    public function render()
    {
        return view('livewire.pages.products.product-detail');
    }
}
