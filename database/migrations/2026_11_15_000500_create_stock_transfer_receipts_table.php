<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('stock_transfer_id')
                ->constrained('stock_transfers')
                ->restrictOnDelete();

            $table->string('receipt_number', 50);

            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('received_at');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['business_id', 'receipt_number'],
                'stock_receipt_business_number_unique'
            );

            $table->index(['business_id', 'stock_transfer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_receipts');
    }
};
