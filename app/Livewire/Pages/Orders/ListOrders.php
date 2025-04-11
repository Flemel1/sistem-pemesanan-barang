<?php

namespace App\Livewire\Pages\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        return $table
            ->query(Order::query()->where('customer_id', $customer->id)->orderBy('created_at', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID Pesanan'),
                Tables\Columns\TextColumn::make('order_date')->label('Tanggal Pesan'),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status Pesanan')
                    ->formatStateUsing(function (string $state) {
                        if ($state == 'wait') {
                            return 'Menunggu Proses Verifikasi Pesanan';
                        } else if ($state == 'accept') {
                            return 'Pesanan Diterima dan Dalam Proses Pengiriman';
                        } else {
                            return 'Pesanan Dibatalkan';
                        }
                    }),
                Tables\Columns\TextColumn::make('order_payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(function (string $state) {
                        return strtoupper($state);
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->label('Detil')
                    ->color('info')
                    ->url(fn (Order $order): string => route('customer.orders.detail', ['order' => $order->id])),
                Action::make('rating')
                    ->label('Beri Rating')
                    ->fillForm(function (Order $order): array {
                        $products = [];
                        foreach ($order->details as $orderDetails) {
                            $product = [
                                'product_id' => $orderDetails->product_id
                            ];
                            array_push($products, $product);
                        }
                        $data['details'] = $products;
                        return $data;
                    })
                    ->form([
                        Repeater::make('details')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Pilih Produk')
                                    ->options(Product::all()->pluck('product_name', 'id'))
                                    ->distinct()
                                    ->searchable()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('review_rating')
                                    ->label('Rating')
                                    ->options([
                                        1 => 1,
                                        2 => 2,
                                        3 => 3,
                                        4 => 4,
                                        5 => 5
                                    ])
                                    ->required(),
                                Textarea::make('review_text')
                                    ->label('Tulis Ulasan Anda')
                                    ->rows(5)
                            ])
                            ->deletable(false)
                            ->reorderable(false)
                            ->addable(false)
                    ])
                    ->action(function (array $data, Order $order): void {
                        $customer = Customer::where('user_id', auth()->id())->first();
                        $products = $data['details'];
                        foreach ($products as $product) {
                            $input['customer_id'] = $customer->id;
                            $input['product_id'] = $product['product_id'];
                            $input['review_rating'] = $product['review_rating'];
                            $input['review_text'] = $product['review_text'];
                            $record = new Review($input);
                            $record->save();
                        }
                        $order->is_reviewed = true;
                        $order->save();
                    })
                    ->hidden(function (Order $record): bool {
                        if ($record->order_status == 'wait') {
                            return true;
                        } else if ($record->order_status == 'accept' && $record->is_reviewed == false) {
                            return false;
                        } else if ($record->order_status == 'accept' && $record->is_reviewed == true) {
                            return true;
                        } else {
                            return true;
                        }
                    }),
                Action::make('pay')
                    ->label('Bayar')
                    ->color('success')
                    ->form([
                        Placeholder::make('transfer')
                            ->label('Nomor Rekening')
                            ->content(fn (): string => '123333333333333333333333 a/n Admin'),
                        FileUpload::make('order_proof_payment')
                            ->label('Bukti Bayar')
                            ->directory('payments')
                            ->visibility('private')
                            ->image()
                            ->required()
                    ])
                    ->action(function (array $data, Order $order): void {
                        try {
                            $order->order_proof_payment = $data['order_proof_payment'];
                            $order->save();
                            $this->createNotification();
                        } catch (Exception $ex) {
                            $this->createNotification(false);
                        }
                    })
                    ->hidden(function (Order $record): bool {
                        if ($record->order_payment_method == 'cod') {
                            return true;
                        } else if ($record->order_status == 'wait' && $record->order_proof_payment == null) {
                            return false;
                        } else if ($record->order_status == 'accept' && $record->review_id == null) {
                            return true;
                        } else if ($record->order_status == 'accept' && $record->review_id != null) {
                            return true;
                        } else {
                            return true;
                        }
                    }),
                Action::make('cancel-order')
                    ->label('Batal')
                    ->color('danger')
                    ->action(function (Order $order): void {
                        $order->delete();
                    })
                    ->hidden(function (Order $order): bool {
                        if ($order->order_payment_method === 'transfer' && $order->order_proof_payment != null) {
                            return true;
                        } else if ($order->order_status === 'wait' && $order->order_payment_method === 'transfer' && $order->is_reviewed == false) {
                            return false;
                        } else {
                            return true;
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    private function createNotification(bool $isSuccess = true)
    {
        if ($isSuccess) {
            Notification::make()
                ->title('Pesanan Telah Dibuat')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Pesanan Gagal Dibuat')
                ->danger()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.pages.orders.list-orders');
    }
}
