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
        Schema::table('types', function (Blueprint $table) {
            $table->integer('Id_Main_Type')->nullable();
        });
        Schema::table('marshallings', function (Blueprint $table) {
            $table->foreign('Id_Type')->references('Id_Type')->on('types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('Id_Main_Type');
        });
    }
};
