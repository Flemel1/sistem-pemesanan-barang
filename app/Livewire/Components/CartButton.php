<?php

namespace App\Livewire\Components;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Livewire\Component;

class CartButton extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public Product $product;

    public function cartAction(): Action
    {
        return Action::make('cart')
            ->label('Keranjang')
            ->size(ActionSize::Large)
            ->form([
                TextInput::make('cart_product_stock')
                    ->label('Jumlah Produk Dibeli')
                    ->required()
                    ->numeric(),
            ])
            ->action(function (array $data): void {
                $customer = Customer::where('user_id', '=', auth()->id())->first();
                $data['customer_id'] = $customer->id;
                $data['product_id'] = $this->product->id;
                $record = new Cart($data);
                $record->save();
            });
    }

    public function render()
    {
        return view('livewire.components.cart-button');
    }
}
