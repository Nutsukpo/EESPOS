<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignId('cashier_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('receipt_number', 50);

            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);
            $table->decimal('tax_amount', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->decimal('amount_paid', 15, 4)->default(0);
            $table->decimal('change_amount', 15, 4)->default(0);

            $table->enum('status', [
                'draft',
                'completed',
                'voided',
                'refunded',
                'partially_refunded',
            ])->default('draft');

            $table->enum('payment_status', [
                'unpaid',
                'partially_paid',
                'paid',
                'refunded',
            ])->default('unpaid');

            $table->timestamp('sold_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['business_id', 'receipt_number'],
                'sales_business_receipt_unique'
            );

            $table->index(['business_id', 'branch_id', 'sold_at']);
            $table->index(['business_id', 'cashier_id', 'sold_at']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
