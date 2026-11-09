<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat record tidak boleh ikut terhapus saat master marshalling dihapus
     * (import Excel / hapus type). Record_List sudah menyimpan salinan datanya sendiri.
     */
    public function up(): void
    {
        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropForeign(['Id_Marshalling']);
        });

        Schema::table('record_lists', function (Blueprint $table) {
            $table->integer('Id_Marshalling')->nullable()->change();
        });

        Schema::table('record_lists', function (Blueprint $table) {
            $table->foreign('Id_Marshalling')->references('Id_Marshalling')->on('marshallings')->nullOnDelete();
        });

        // foto_parts tidak dipakai lagi; cukup lepas FK-nya agar tidak ikut terhapus
        Schema::table('foto_parts', function (Blueprint $table) {
            $table->dropForeign(['Id_Marshalling']);
        });
    }

    /** Catatan: down() gagal jika sudah ada record_lists.Id_Marshalling bernilai NULL. */
    public function down(): void
    {
        Schema::table('foto_parts', function (Blueprint $table) {
            $table->foreign('Id_Marshalling')->references('Id_Marshalling')->on('marshallings')->onDelete('cascade');
        });

        Schema::table('record_lists', function (Blueprint $table) {
            $table->dropForeign(['Id_Marshalling']);
        });

        Schema::table('record_lists', function (Blueprint $table) {
            $table->integer('Id_Marshalling')->nullable(false)->change();
        });

        Schema::table('record_lists', function (Blueprint $table) {
            $table->foreign('Id_Marshalling')->references('Id_Marshalling')->on('marshallings')->onDelete('cascade');
        });
    }
};
