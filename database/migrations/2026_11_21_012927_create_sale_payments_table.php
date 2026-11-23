<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('payment_method', [
                'cash',
                'mobile_money',
                'card',
                'bank_transfer',
                'other',
            ]);

            $table->decimal('amount', 15, 4);
            $table->string('reference', 100)->nullable();
            $table->string('provider', 100)->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'cancelled',
            ])->default('completed');

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->index(['sale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
