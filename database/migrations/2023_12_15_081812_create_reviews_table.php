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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->text('review_text')->nullable();
            $table->tinyInteger('review_rating');
            $table->foreignId('product_id')->constrained(table: 'products', indexName: 'product_review_id');
            $table->foreignId('customer_id')->constrained(table: 'customers', indexName: 'customer_review_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
