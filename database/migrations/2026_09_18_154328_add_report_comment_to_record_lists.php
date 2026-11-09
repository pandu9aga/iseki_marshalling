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
        Schema::table('record_lists', function (Blueprint $table) {
            $table->text('Report_Comment')->nullable()->default(null)->after('Reporter_Nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn('Report_Comment');
        });
    }
};
