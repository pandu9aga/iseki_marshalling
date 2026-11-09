<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Foto part sekarang dari aplikasi iseki_recorder; tabel lama tidak dipakai lagi.
    public function up(): void
    {
        Schema::dropIfExists('foto_parts');
    }

    public function down(): void
    {
        // Tanpa FK ke marshallings (sudah dilepas migrasi 2026_11_03_000001)
        Schema::create('foto_parts', function (Blueprint $table) {
            $table->id();
            $table->integer('Id_Marshalling')->unique();
            $table->integer('Sequence_No');
            $table->string('Code_Rack');
            $table->string('Name_Part');
            $table->string('Photo_Path');
            $table->timestamps();
        });
    }
};
