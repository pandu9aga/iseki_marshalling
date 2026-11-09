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
        Schema::create('foto_parts', function (Blueprint $table) {
            $table->id();
            $table->integer('Id_Marshalling')->unique();
            $table->integer('Sequence_No');
            $table->string('Code_Rack');
            $table->string('Name_Part');
            $table->string('Photo_Path');
            $table->timestamps();

            $table->foreign('Id_Marshalling')->references('Id_Marshalling')->on('marshallings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_parts');
    }
};
