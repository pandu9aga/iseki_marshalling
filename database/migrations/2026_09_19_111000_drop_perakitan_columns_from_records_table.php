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
            $columnsToDrop = [];
            if (Schema::hasColumn('records', 'Perakitan_Comment')) {
                $columnsToDrop[] = 'Perakitan_Comment';
            }
            if (Schema::hasColumn('records', 'Perakitan_Nik')) {
                $columnsToDrop[] = 'Perakitan_Nik';
            }
            if (Schema::hasColumn('records', 'Perakitan_Comment_Time')) {
                $columnsToDrop[] = 'Perakitan_Comment_Time';
            }
            if (Schema::hasColumn('records', 'Perakitan_Comment_Status')) {
                $columnsToDrop[] = 'Perakitan_Comment_Status';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->text('Perakitan_Comment')->nullable()->default(null)->after('Remark');
            $table->string('Perakitan_Nik', 20)->nullable()->default(null)->after('Perakitan_Comment');
            $table->dateTime('Perakitan_Comment_Time')->nullable()->default(null)->after('Perakitan_Nik');
            $table->enum('Perakitan_Comment_Status', ['pending', 'oke'])->default('pending')->after('Perakitan_Comment_Time');
        });
    }
};
