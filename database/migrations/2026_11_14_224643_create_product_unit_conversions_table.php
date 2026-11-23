<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_unit_conversions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_unit_id')
                ->constrained('unit_of_measures')
                ->cascadeOnDelete();

            $table->foreignId('to_unit_id')
                ->constrained('unit_of_measures')
                ->cascadeOnDelete();

            $table->decimal('conversion_factor', 15, 6);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique([
                'product_id',
                'from_unit_id',
                'to_unit_id',
            ], 'product_unit_conversion_unique');

            $table->index(['business_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_unit_conversions');
    }
};
