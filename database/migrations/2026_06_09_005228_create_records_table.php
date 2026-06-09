<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->integer('Id_Record', true);
            $table->integer('Id_User');
            $table->string('Sequence_No_Record', 255);
            $table->string('Production_Date_Record', 255);
            $table->string('Type', 255);
            $table->string('Area', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
