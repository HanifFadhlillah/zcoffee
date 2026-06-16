<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->string('menu_name'); // snapshot nama saat order
            $table->unsignedBigInteger('menu_price'); // snapshot harga saat order
            $table->unsignedInteger('quantity');
            $table->enum('sugar_level', ['less', 'normal', 'extra'])->default('normal');
            $table->enum('ice_level', ['no_ice', 'less', 'normal'])->default('normal');
            $table->unsignedBigInteger('subtotal');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
