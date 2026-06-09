<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types', function (Blueprint $table) {
            $table->integer('Id_Type', true);
            $table->string('Type', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types');
    }
};
