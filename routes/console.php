<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\MainType;
use App\Models\Type;
use App\Models\Marshalling;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sxg3:reset-types {--force : Jalankan tanpa konfirmasi}', function () {
    $newTypes = [
        'SXG327E42S',
        'SXG327GE42S',
        'SXG324E42S',
        'SXG324GE42S',
        'SXG327KRE42S',
        'SXG324KRE42S',
    ];

    $mainType = MainType::firstOrCreate(['Main_Type' => 'SXG3']);

    // Ambil semua tipe yang berelasi dengan MainType SXG3 atau yang namanya diawali SXG3
    $existingTypes = Type::where('Id_Main_Type', $mainType->Id_Main_Type)
        ->orWhere('Type', 'LIKE', 'SXG3%')
        ->get();

    $existingTypeIds = $existingTypes->pluck('Id_Type')->toArray();
    $existingCount = count($existingTypeIds);

    $marshallingCount = 0;
    if (!empty($existingTypeIds)) {
        $marshallingCount = Marshalling::whereIn('Id_Type', $existingTypeIds)->count();
    }

    $this->info("Ditemukan {$existingCount} tipe SXG3 lama dengan total {$marshallingCount} data marshalling.");

    if (!$this->option('force')) {
        if (!$this->confirm('Apakah Anda yakin ingin menghapus semua tipe SXG3 ini beserta list marshalling-nya dan menginsert 6 tipe master baru?')) {
            $this->warn('Operasi dibatalkan.');
            return 0;
        }
    }

    DB::transaction(function () use ($existingTypeIds, $newTypes, $mainType) {
        // 1. Hapus marshalling lama
        if (!empty($existingTypeIds)) {
            Marshalling::whereIn('Id_Type', $existingTypeIds)->delete();
            // 2. Hapus tipe lama
            Type::whereIn('Id_Type', $existingTypeIds)->delete();
        }

        // 3. Insert 6 tipe master SXG3 baru
        $maxId = Type::max('Id_Type') ?? 0;
        foreach ($newTypes as $typeName) {
            $maxId++;
            Type::create([
                'Id_Type'      => $maxId,
                'Type'         => $typeName,
                'Id_Main_Type' => $mainType->Id_Main_Type,
            ]);
        }
    });

    $this->newLine();
    $this->info("✓ Berhasil menghapus {$existingCount} tipe lama dan {$marshallingCount} list marshalling.");
    $this->info("✓ Berhasil mendaftarkan 6 tipe master SXG3 baru ke kategori {$mainType->Main_Type}:");
    foreach ($newTypes as $t) {
        $this->line("   - {$t}");
    }

    return 0;
})->purpose('Menghapus semua tipe SXG3 lama dan marshallingnya, lalu menginsert 6 tipe master SXG3 baru');
