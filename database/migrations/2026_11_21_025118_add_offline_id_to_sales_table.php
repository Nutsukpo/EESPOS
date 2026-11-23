<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->uuid('offline_id')->nullable()->after('receipt_number');

            $table->unique(
                ['business_id', 'offline_id'],
                'sales_business_offline_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_business_offline_unique');
            $table->dropColumn('offline_id');
        });
    }
};
