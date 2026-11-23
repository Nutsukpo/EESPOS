<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('stock_transfer_id')
                ->constrained('stock_transfers')
                ->cascadeOnDelete();

            $table->foreignId('product_variation_id')
                ->constrained('product_variations')
                ->restrictOnDelete();

            $table->decimal('quantity_requested', 15, 4);
            $table->decimal('quantity_dispatched', 15, 4)->default(0);
            $table->decimal('quantity_received', 15, 4)->default(0);
            $table->decimal('quantity_damaged', 15, 4)->default(0);
            $table->decimal('quantity_missing', 15, 4)->default(0);

            $table->text('discrepancy_notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['stock_transfer_id', 'product_variation_id'],
                'stock_transfer_variation_unique'
            );

            $table->index(['business_id', 'product_variation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
