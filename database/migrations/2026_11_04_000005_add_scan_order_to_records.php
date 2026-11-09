<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Urutan scan saat record dibuat ('box'|'sequence'); NULL (record lama) diperlakukan 'sequence'
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->string('Scan_Order', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('Scan_Order');
        });
    }
};
