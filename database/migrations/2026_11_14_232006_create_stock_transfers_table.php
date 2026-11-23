<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('to_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('reference_number', 50);

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'in_transit',
                'completed',
                'rejected',
                'cancelled',
            ])->default('draft');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('dispatched_at')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')->nullable();

            $table->text('notes')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->unique(
                ['business_id', 'reference_number'],
                'stock_transfer_business_reference_unique'
            );

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'from_branch_id']);
            $table->index(['business_id', 'to_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
