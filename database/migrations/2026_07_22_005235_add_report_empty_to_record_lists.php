<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->datetime('Report_Empty')->nullable()->default(null)->after('Is_Empty');
            $table->string('Reporter_Nik', 20)->nullable()->default(null)->after('Report_Empty');
        });
    }

    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn(['Report_Empty', 'Reporter_Nik']);
        });
    }
};
