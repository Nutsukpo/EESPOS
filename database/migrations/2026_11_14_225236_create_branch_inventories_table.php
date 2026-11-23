<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_variation_id')
                ->constrained('product_variations')
                ->cascadeOnDelete();

            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('minimum_stock', 15, 4)->default(0);
            $table->decimal('reorder_level', 15, 4)->default(0);
            $table->decimal('reorder_quantity', 15, 4)->default(0);

            $table->timestamp('last_stocked_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'product_variation_id'],
                'branch_inventory_variation_unique'
            );

            $table->index(['business_id', 'branch_id']);
            $table->index(['business_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventories');
    }
};
