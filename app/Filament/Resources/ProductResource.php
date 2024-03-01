<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')->label('Nama Produk')->required(),
                Forms\Components\TextInput::make('product_stock')->label('Stok Produk')->numeric()->required(),
                Forms\Components\TextInput::make('product_price')->label('Harga Produk')->numeric()->required(),
                Forms\Components\TextInput::make('product_discount')->label('Diskon Produk (%)')->numeric()->required(),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::all()->pluck('category_name', 'id'))
                    ->required(),
                Forms\Components\Select::make('product_unit')
                    ->label('Satuan')
                    ->options([
                        'pcs' => 'Pcs',
                        'renteng' => 'Renteng',
                        'karung' => 'Karung'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('product_description')->label('Deskripsi Produk')->rows(10)
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\FileUpload::make('product_photo')
                    ->label('Foto Produk')
                    ->directory('products')
                    ->visibility('private')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\ImageColumn::make('product_photo')
                    ->label('Foto')
                    ->height(200),
                Tables\Columns\TextColumn::make('product_name')->label('Nama Produk'),
                Tables\Columns\TextColumn::make('product_price')->label('Harga Produk'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public function viewAny(): bool
    {
        return false;
    }
}
