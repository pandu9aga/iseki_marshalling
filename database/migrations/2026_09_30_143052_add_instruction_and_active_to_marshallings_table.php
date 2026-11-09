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
        Schema::table('marshallings', function (Blueprint $table) {
            $table->string('No_Instruction')->nullable();
            $table->boolean('Is_Active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marshallings', function (Blueprint $table) {
            $table->dropColumn(['No_Instruction', 'Is_Active']);
        });
    }
};
