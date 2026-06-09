<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->string('Image_Ng', 255)->nullable()->after('Time_Record');
        });
    }

    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn('Image_Ng');
        });
    }
};
