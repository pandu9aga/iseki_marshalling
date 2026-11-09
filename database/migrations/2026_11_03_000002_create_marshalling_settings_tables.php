<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marshalling_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        DB::table('marshalling_settings')->insert([
            'key' => 'scan_order', 'value' => 'box', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // sequence_no = No. Sequence produksi (records.Sequence_No_Record), bukan Marshalling.Sequence_No
        Schema::create('scan_order_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('sequence_no', 50)->unique();
            $table->enum('mode', ['box', 'sequence']);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_order_overrides');
        Schema::dropIfExists('marshalling_settings');
    }
};
