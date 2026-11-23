<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
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

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('movement_type', [
                'opening_stock',
                'purchase',
                'sale',
                'customer_return',
                'supplier_return',
                'adjustment',
                'damaged',
                'expired',
                'transfer_in',
                'transfer_out',
            ]);

            $table->enum('direction', [
                'in',
                'out',
            ]);

            $table->decimal('quantity', 15, 4);

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('moved_at');

            $table->timestamps();

            $table->index(['business_id', 'branch_id', 'moved_at']);
            $table->index(['product_variation_id', 'moved_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
