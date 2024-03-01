<?php

namespace App\Livewire\Pages\Orders;

use App\Models\Product;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class CreateOrder extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('products')
                    ->schema([
                        Select::make('product_id')
                            ->label('Pilih Produk')
                            ->options(Product::all()->pluck('product_name', 'id'))
                            ->required()
                            ->distinct()
                            ->searchable(),
                        TextInput::make('order_product_stock')
                            ->label('Jumlah Produk Dibeli')
                            ->required()
                            ->numeric(),
                    ])
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->addActionLabel('Tambah Produk')
                    ->minItems(1)
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('order_address')
                    ->label('Alamat Pengiriman')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Select::make('order_payment_method')
                    ->options(['cod' => 'COD', 'transfer' => 'Transfer'])
                    ->required(),
                Map::make('location')
                    ->defaultLocation([-7.7860101, 110.3787211])
                    ->clickable()
                    ->defaultZoom(15)
                    ->geolocate()
                    ->geolocateLabel('Lokasi Saat Ini')
                    ->required()
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        
    }

    public function render(): View
    {
        return view('livewire.pages.orders.create-order');
    }
}