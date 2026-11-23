<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('stock_transfer_receipt_id')
                ->constrained('stock_transfer_receipts')
                ->cascadeOnDelete();

            $table->foreignId('stock_transfer_item_id')
                ->constrained('stock_transfer_items')
                ->restrictOnDelete();

            $table->foreignId('product_variation_id')
                ->constrained('product_variations')
                ->restrictOnDelete();

            $table->decimal('quantity_received', 15, 4)->default(0);
            $table->decimal('quantity_damaged', 15, 4)->default(0);
            $table->decimal('quantity_missing', 15, 4)->default(0);

            $table->text('discrepancy_notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['stock_transfer_receipt_id', 'stock_transfer_item_id'],
                'stock_receipt_item_line_unique'
            );

            $table->index(
                ['business_id', 'product_variation_id'],
                'stock_receipt_item_business_variation_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_receipt_items');
    }
};
