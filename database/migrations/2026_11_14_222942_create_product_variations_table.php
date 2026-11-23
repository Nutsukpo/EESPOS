<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name')->default('Default');
            $table->string('sku');
            $table->json('attributes')->nullable();

            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('selling_price', 15, 4)->default(0);

            $table->enum('tax_status', [
                'taxable',
                'exempt',
                'zero_rated',
            ])->default('taxable');

            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->boolean('tax_inclusive')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'sku']);
            $table->index(['business_id', 'product_id']);
            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
