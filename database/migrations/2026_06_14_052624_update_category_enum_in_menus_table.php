<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah nilai 'maincourse' dan 'snack' ke ENUM category pada tabel menus.
     * Menggunakan raw SQL karena Laravel Blueprint tidak mendukung ALTER ENUM langsung.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE menus MODIFY COLUMN category ENUM('espresso', 'manual', 'noncoffee', 'maincourse', 'snack') NOT NULL");
    }

    /**
     * Rollback: kembalikan ke ENUM semula.
     * Pastikan tidak ada data dengan nilai 'maincourse' atau 'snack' sebelum rollback.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE menus MODIFY COLUMN category ENUM('espresso', 'manual', 'noncoffee') NOT NULL");
    }
};
