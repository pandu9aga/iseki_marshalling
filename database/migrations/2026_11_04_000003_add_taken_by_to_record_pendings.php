<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Member (id rifa, tanpa FK) yang mengambil alih pending ini
    public function up(): void
    {
        Schema::table('record_pendings', function (Blueprint $table) {
            $table->unsignedBigInteger('Taken_By')->nullable()->after('Resumed_At');
        });
    }

    public function down(): void
    {
        Schema::table('record_pendings', function (Blueprint $table) {
            $table->dropColumn('Taken_By');
        });
    }
};
