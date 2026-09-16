<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum kolom Area di tabel marshallings dan record_lists agar mendukung transmisi_a, transmisi_b, transmisi_c
        DB::statement("ALTER TABLE `marshallings` MODIFY COLUMN `Area` ENUM('sub_assy', 'sub_engine', 'transmisi', 'transmisi_a', 'transmisi_b', 'transmisi_c', 'main_line', 'mowcol', 'front_axle') NOT NULL");
        DB::statement("ALTER TABLE `record_lists` MODIFY COLUMN `Area` ENUM('sub_assy', 'sub_engine', 'transmisi', 'transmisi_a', 'transmisi_b', 'transmisi_c', 'main_line', 'mowcol', 'front_axle') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `marshallings` MODIFY COLUMN `Area` ENUM('sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle') NOT NULL");
        DB::statement("ALTER TABLE `record_lists` MODIFY COLUMN `Area` ENUM('sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle') NOT NULL");
    }
};
