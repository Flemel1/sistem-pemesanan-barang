<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\HistoryResource\Pages;
use App\Filament\Customer\Resources\HistoryResource\RelationManagers;
use App\Models\Order;
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
                InfoLists\Components\TextEntry::make('product.product_name')->label('Nama Produk'),
                InfoLists\Components\TextEntry::make('order_product_stock')->label('Jumlah (Pcs)'),
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
                Tables\Columns\TextColumn::make('product.product_name')->label('Nama Produk'),
                Tables\Columns\TextColumn::make('order_product_stock')->label('Jumlah (Pcs)')
                    ->numeric(),
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
                    ->form([
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
                    ->action(function (array $data, Order $order): void {
                        $data['customer_id'] = auth()->id();
                        $data['product_id'] = $order->product->id;
                        $record = new Review($data);
                        $record->save();
                        $order->review_id = $record->id;
                        $order->save();
                    })
                    ->hidden(function (Order $record): bool {
                        if ($record->order_status == 'wait') {
                            return true;
                        } else if ($record->order_status == 'accept' && $record->review_id == null) {
                            return false;
                        } else if ($record->order_status == 'accept' && $record->review_id != null) {
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
        return parent::getEloquentQuery()->where('customer_id', auth()->id())->withoutGlobalScopes();
    }
}
