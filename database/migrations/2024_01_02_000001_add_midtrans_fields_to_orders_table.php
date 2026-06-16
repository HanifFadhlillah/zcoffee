<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Token Snap dari Midtrans — digunakan untuk membuka popup pembayaran
            $table->string('snap_token')->nullable()->after('customer_note');

            // URL redirect Snap (opsional, sebagai fallback jika popup tidak bisa dibuka)
            $table->string('snap_url')->nullable()->after('snap_token');

            // ID transaksi dari Midtrans (dikembalikan di webhook)
            $table->string('midtrans_transaction_id')->nullable()->after('snap_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'snap_url', 'midtrans_transaction_id']);
        });
    }
};
