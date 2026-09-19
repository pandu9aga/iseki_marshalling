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
        Schema::table('records', function (Blueprint $table) {
            $table->text('Perakitan_Comment')->nullable()->default(null)->after('Remark');
            $table->string('Perakitan_Nik', 20)->nullable()->default(null)->after('Perakitan_Comment');
            $table->dateTime('Perakitan_Comment_Time')->nullable()->default(null)->after('Perakitan_Nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn(['Perakitan_Comment', 'Perakitan_Nik', 'Perakitan_Comment_Time']);
        });
    }
};
