<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->boolean('Is_Empty')->nullable()->default(null)->after('Status_Ng');
        });
    }

    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn('Is_Empty');
        });
    }
};
