<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. ZC-20240101-001
            $table->unsignedInteger('table_number');
            $table->unsignedBigInteger('total_price');
            $table->enum('payment_method', ['qris', 'cash']);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])
                  ->default('pending');
            $table->string('customer_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('table_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
