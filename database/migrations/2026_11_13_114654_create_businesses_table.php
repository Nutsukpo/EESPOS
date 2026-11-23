<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('business_type')->default('retail');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->text('address')->nullable();

            $table->string('currency', 3)->default('GHS');

            $table->string('tax_number')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};