<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
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

            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            $table->decimal('received_quantity', 15, 4)->default(0);
            $table->decimal('quantity', 15, 4)->default(0);

            $table->timestamp('received_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'business_id',
                'branch_id',
                'product_variation_id',
                'expiry_date',
            ], 'stock_batches_fefo_index');

            $table->index('batch_number');
        });

        // Preserve every existing inventory balance as an opening batch.
        // No existing inventory quantity or movement record is modified.
        DB::table('branch_inventories')
            ->orderBy('id')
            ->each(function ($inventory) {
                DB::table('stock_batches')->insert([
                    'business_id' => $inventory->business_id,
                    'branch_id' => $inventory->branch_id,
                    'product_variation_id' => $inventory->product_variation_id,
                    'batch_number' => null,
                    'expiry_date' => null,
                    'received_quantity' => $inventory->quantity,
                    'quantity' => $inventory->quantity,
                    'received_at' => $inventory->last_stocked_at,
                    'created_by' => null,
                    'notes' => 'Opening balance migrated from existing branch inventory. Original batch number and expiry date unknown.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
