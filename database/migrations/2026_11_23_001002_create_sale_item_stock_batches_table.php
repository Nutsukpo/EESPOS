<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_item_stock_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->restrictOnDelete();

            $table->foreignId('stock_batch_id')
                ->constrained('stock_batches')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 4);

            $table->timestamps();

            $table->unique(
                ['sale_item_id', 'stock_batch_id'],
                'sale_item_stock_batches_unique'
            );

            $table->index('stock_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_stock_batches');
    }
};
