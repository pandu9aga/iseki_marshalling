<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_lists', function (Blueprint $table) {
            $table->integer('Id_Record_List', true);
            $table->integer('Id_Record');
            $table->integer('Id_Marshalling');
            $table->string('Code_Part', 255);
            $table->string('Name_Part', 255);
            $table->string('Code_Rack', 255);
            $table->string('Difference', 255);
            $table->string('Location_Rack', 255);
            $table->string('Box', 255);
            $table->integer('Qty');
            $table->enum('Mode', ['manual', 'ai'])->default('manual');
            $table->enum('Area', ['sub_assy', 'sub_engine', 'transmisi', 'main_line', 'mowcol', 'front_axle']);
            $table->integer('Sequence_No');
            $table->integer('Qty_Record')->nullable();
            $table->datetime('Time_Record')->nullable();

            $table->foreign('Id_Record')->references('Id_Record')->on('records')->onDelete('cascade');
            $table->foreign('Id_Marshalling')->references('Id_Marshalling')->on('marshallings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_lists');
    }
};
