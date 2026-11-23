<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_receipt_items', function (Blueprint $table) {
            $table->enum('discrepancy_status', [
                'not_applicable',
                'pending',
                'resolved',
            ])->default('not_applicable')->after('discrepancy_notes');

            $table->foreignId('resolved_by')
                ->nullable()
                ->after('discrepancy_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable()->after('resolved_by');

            $table->text('resolution_notes')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);

            $table->dropColumn([
                'discrepancy_status',
                'resolved_by',
                'resolved_at',
                'resolution_notes',
            ]);
        });
    }
};
