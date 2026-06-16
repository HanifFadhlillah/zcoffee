<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Untuk menyimpan pilihan hot/ice pada menu yang bisa disajikan dua cara (mis. Cappuccino)
            $table->string('serve_type')->nullable()->after('ice_level');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('serve_type');
        });
    }
};
