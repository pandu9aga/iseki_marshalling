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
        Schema::create('map_areas', function (Blueprint $table) {
            $table->id();
            $table->string('Area')->nullable();
            $table->string('Location_Rack')->nullable();
            $table->integer('Sequence_No')->nullable();
            $table->timestamps();

            $table->unique(['Area', 'Sequence_No']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_areas');
    }
};
