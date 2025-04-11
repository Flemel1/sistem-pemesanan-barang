<?php

namespace App\Livewire\Components;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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
                try {
                    $customer = Customer::where('user_id', '=', auth()->id())->first();

                    $cart_items = Cart::where('customer_id', $customer->id)->where('product_id', $this->product->id)->get();
                    if (sizeof($cart_items) == 0) {
                        $data['customer_id'] = $customer->id;
                        $data['product_id'] = $this->product->id;
                        $record = new Cart($data);
                        $record->save();
                    } else {
                        $cart_item = $cart_items->first();
                        $cart_item->cart_product_stock += $data['cart_product_stock'];
                        $cart_item->save();
                    }
                    $this->createNotification();
                } catch (Exception $ex) {
                    $this->createNotification(false);
                }
                
            });
    }

    private function createNotification(bool $isSuccess = true)
    {
        if ($isSuccess) {
            Notification::make()
                ->title('Disimpan Dikeranjang')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal Menyimpan Dikeranjang')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.components.cart-button');
    }
}
