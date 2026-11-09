<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // NULL = milik pemilik record; isi = id member yang membantu scan box ini (tanpa FK, member ada di DB rifa)
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('Assigned_To')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropColumn('Assigned_To');
        });
    }
};
