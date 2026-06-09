<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marshallings', function (Blueprint $table) {
            $table->integer('Id_Marshalling', true);
            $table->integer('Id_Type');
            $table->integer('Sequence_No');
            $table->string('Code_Part', 255);
            $table->string('Name_Part', 255);
            $table->string('Code_Rack', 255);
            $table->string('Difference', 255);
            $table->string('Location_Rack', 255);
            $table->string('Box', 255);
            $table->integer('Qty');
            $table->enum('Mode', ['manual', 'ai'])->default('manual');
            $table->enum('Area', ['sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle']);

            $table->foreign('Id_Type')->references('Id_Type')->on('types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marshallings');
    }
};
