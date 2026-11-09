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
        Schema::table('member_areas', function (Blueprint $table) {
            $table->string('audio_name', 255)->nullable()->default(null)->after('area');
        });

        Schema::table('records', function (Blueprint $table) {
            $table->enum('Perakitan_Comment_Status', ['pending', 'oke'])->default('pending')->after('Perakitan_Comment_Time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_areas', function (Blueprint $table) {
            $table->dropColumn('audio_name');
        });

        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('Perakitan_Comment_Status');
        });
    }
};
