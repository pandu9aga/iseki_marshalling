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
        Schema::table('part_kurangs', function (Blueprint $table) {
            $table->dateTime('dismissed_at')->nullable()->after('received_time')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_kurangs', function (Blueprint $table) {
            $table->dropColumn('dismissed_at');
        });
    }
};
