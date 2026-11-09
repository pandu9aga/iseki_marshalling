<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Pending per member per record; aktif = Resumed_At NULL. Id_User tanpa FK (member ada di DB rifa).
    public function up(): void
    {
        Schema::create('record_pendings', function (Blueprint $table) {
            $table->id();
            $table->integer('Id_Record')->index();
            $table->unsignedBigInteger('Id_User')->index();
            $table->string('Reason', 255);
            $table->dateTime('Pending_At');
            $table->dateTime('Resumed_At')->nullable();
            $table->timestamps();

            $table->foreign('Id_Record')->references('Id_Record')->on('records')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_pendings');
    }
};
