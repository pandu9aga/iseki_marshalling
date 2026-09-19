<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('part_kurangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_record')->nullable()->index();
            $table->string('sequence_no', 50)->nullable()->index();
            $table->string('production_date', 50)->nullable()->index();
            $table->string('type', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->unsignedBigInteger('id_user')->nullable()->index();
            $table->string('member_nik', 50)->nullable()->index();
            $table->string('perakitan_nik', 50)->nullable()->index();
            $table->text('comment');
            $table->dateTime('comment_time')->nullable();
            $table->enum('status', ['pending', 'oke'])->default('pending')->index();
            $table->dateTime('received_time')->nullable();
            $table->timestamps();
        });

        // Pindahkan data existing jika ada catatan part kurang di tabel records
        $existingRecords = DB::table('records')
            ->whereNotNull('Perakitan_Comment')
            ->where('Perakitan_Comment', '!=', '')
            ->get();

        foreach ($existingRecords as $rec) {
            $memberNik = DB::table('members')->where('id', $rec->Id_User)->value('nik');

            DB::table('part_kurangs')->insert([
                'id_record'       => $rec->Id_Record,
                'sequence_no'     => $rec->Sequence_No_Record,
                'production_date' => $rec->Production_Date_Record,
                'type'            => $rec->Type,
                'area'            => $rec->Area,
                'id_user'         => $rec->Id_User,
                'member_nik'      => $memberNik,
                'perakitan_nik'   => $rec->Perakitan_Nik,
                'comment'         => $rec->Perakitan_Comment,
                'comment_time'    => $rec->Perakitan_Comment_Time ?: now(),
                'status'          => $rec->Perakitan_Comment_Status ?: 'pending',
                'received_time'   => ($rec->Perakitan_Comment_Status === 'oke') ? now() : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_kurangs');
    }
};
