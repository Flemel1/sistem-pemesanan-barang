<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\HistoryResource\Pages;
use App\Filament\Customer\Resources\HistoryResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class HistoryResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoLists\Components\TextEntry::make('details.product.product_name')
                    ->label('Nama Produk')
                    ->listWithLineBreaks(),
                InfoLists\Components\TextEntry::make('details.order_product_stock')
                    ->label('Jumlah (Pcs)')
                    ->listWithLineBreaks(),
                InfoLists\Components\TextEntry::make('order_date')->label('Tanggal Pesan')->date(),
                InfoLists\Components\TextEntry::make('order_payment_method')->label('Metode Pembayaran'),
                InfoLists\Components\TextEntry::make('customer.customer_name')->label('Nama Pemesan'),
                InfoLists\Components\TextEntry::make('order_address')->label('Alamat Pengantaran'),
                InfoLists\Components\TextEntry::make('order_status')->label('Status Pengiriman')
                    ->formatStateUsing(function (string $state) {
                        if ($state == 'wait') {
                            return 'Menunggu Proses Verifikasi Pesanan';
                        } else if ($state == 'accept') {
                            return 'Pesanan Diterima dan Dalam Proses Pengiriman';
                        } else {
                            return 'Pesanan Dibatalkan';
                        }
                    }),
                InfoLists\Components\ImageEntry::make('order_proof_payment')->label('Bukti Bayar'),
                InfoLists\Components\TextEntry::make('order_charge')->label('Jumlah yang Dibayar (Rupiah)')->money('IDR'),
                InfoLists\Components\TextEntry::make('order_deliver_fee')->label('Ongkos Kirim')->money('IDR'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID Pesanan'),
                // Tables\Columns\TextColumn::make('product.product_name')->label('Nama Produk'),
                // Tables\Columns\TextColumn::make('order_product_stock')->label('Jumlah (Pcs)')
                //     ->numeric(),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
                        Forms\Components\Repeater::make('details')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Pilih Produk')
                                    ->options(Product::all()->pluck('product_name', 'id'))
                                    ->distinct()
                                    ->searchable()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('review_rating')
                                    ->label('Rating')
                                    ->options([
                                        1 => 1,
                                        2 => 2,
                                        3 => 3,
                                        4 => 4,
                                        5 => 5
                                    ])
                                    ->required(),
                                Forms\Components\Textarea::make('review_text')
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
                    ->form([
                        Forms\Components\FileUpload::make('order_proof_payment')
                            ->label('Bukti Bayar')
                            ->directory('payments')
                            ->visibility('private')
                            ->image()
                            ->required()
                    ])
                    ->action(function (array $data, Order $order): void {
                        $order->order_proof_payment = $data['order_proof_payment'];
                        $order->save();
                    })
                    ->hidden(function (Order $record): bool {
                        if ($record->order_status == 'wait' && $record->order_proof_payment == null) {
                            return false;
                        } else if ($record->order_status == 'accept' && $record->review_id == null) {
                            return true;
                        } else if ($record->order_status == 'accept' && $record->review_id != null) {
                            return true;
                        } else {
                            return true;
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistories::route('/'),
            'create' => Pages\CreateHistory::route('/create'),
            'view' => Pages\ViewHistory::route('/{record}'),
            'edit' => Pages\EditHistory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        return parent::getEloquentQuery()->where('customer_id', $customer->id)->withoutGlobalScopes();
    }
}
