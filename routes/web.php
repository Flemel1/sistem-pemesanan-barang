<?php

use App\Http\Controllers\ReportGenerator;
use App\Livewire\Pages\Carts\ListCarts;
use App\Livewire\Pages\CustomerHome;
use App\Livewire\Pages\LoginPage;
use App\Livewire\Pages\Orders\CreateOrder;
use App\Livewire\Pages\Orders\DetailOrder;
use App\Livewire\Pages\Orders\ListOrders;
use App\Livewire\Pages\Products\ProductDetail;
use App\Livewire\Pages\RegisterPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/', CustomerHome::class)->name('customer.index');
    Route::get('customer/products/{product}', ProductDetail::class)->name('customer.products.detail');
    Route::get('customer/carts', ListCarts::class)->name('customer.carts');
    Route::get('customer/orders', ListOrders::class)->name('customer.orders');
    Route::get('customer/orders/{order}', DetailOrder::class)->name('customer.orders.detail');
    Route::post('logout', function () {
        auth()->logout();
        return redirect('login');
    })->name('logout');
    Route::get('generate-report', [ReportGenerator::class, 'index'])->name('generate-report');
    Route::get('generate-report-verify', [ReportGenerator::class, 'report_verify'])->name('generate-report-verify');
});
Route::get('login', LoginPage::class)->name('login');
Route::get('register', RegisterPage::class)->name('register');
Route::view('report-test','reports.order-verify-report');
