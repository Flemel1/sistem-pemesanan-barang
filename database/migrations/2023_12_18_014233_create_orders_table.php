<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamp('order_date')->useCurrent();
            $table->text('order_address');
            $table->enum('order_payment_method', ['cod', 'transfer']);
            $table->enum('order_status', ['wait', 'reject', 'accept'])->default('wait');
            $table->string('order_proof_payment')->nullable();
            $table->integer('order_product_stock');
            $table->foreignId('customer_id')->constrained(table: 'customers', indexName: 'customer_order_id');
            $table->foreignId('product_id')->constrained(table: 'products', indexName: 'product_order_id');
            $table->foreignId('review_id')->nullable()->constrained(table: 'reviews', indexName: 'review_order_id')->onUpdate('cascade')->onDelete('set null');
            $table->integer('order_charge');
            $table->integer('order_deliver_fee');
            $table->point('location');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
