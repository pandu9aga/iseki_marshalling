<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->string('Status_Ng', 20)->nullable()->after('Image_Ng');
        });
    }

    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn('Status_Ng');
        });
    }
};
