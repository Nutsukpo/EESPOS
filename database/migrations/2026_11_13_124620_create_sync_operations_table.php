<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_operations', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('device_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->uuid('operation_uuid');
        
            $table->string('operation_type');
        
            $table->json('payload');
        
            $table->string('status')->default('pending');
        
            $table->timestamp('processed_at')->nullable();
        
            $table->text('error_message')->nullable();
        
            $table->timestamps();
        
            $table->unique([
                'business_id',
                'operation_uuid',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_operations');
    }
};
