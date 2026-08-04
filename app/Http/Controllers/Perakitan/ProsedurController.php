<?php

namespace App\Http\Controllers\Perakitan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProsedurController extends Controller
{
    private function getListReports()
    {
        $user = Auth::guard('perakitan')->user();
        $nik = $user ? $user->nik : null;

        if (!$nik) return collect();

        $now = Carbon::now();
        $cutoff = Carbon::parse('2026-08-01');
        $memberId = null;

        if ($now->lt($cutoff)) {
            $member = DB::connection('aspro')
                ->table('members')
                ->where('NIK_Member', $nik)
                ->first();
            if ($member) {
                $memberId = $member->Id_Member;
            }
        } else {
            $memberId = $user->id;
        }

        if (!$memberId) return collect();

        return DB::connection('aspro')
            ->table('reports as r')
            ->join('list_reports as lr', 'r.Id_Report', '=', 'lr.Id_Report')
            ->where('r.Id_Member', $memberId)
            ->whereYear('r.Start_Report', $now->year)
            ->whereMonth('r.Start_Report', $now->month)
            ->select('lr.Name_Procedure', 'lr.Name_Area', 'lr.Name_Tractor')
            ->distinct()
            ->get();
    }

    public function index()
    {
        $listReports = $this->getListReports();
        $tractorNames = $listReports->pluck('Name_Tractor')->unique()->values();

        $tractorPhotos = DB::connection('aspro')
            ->table('tractors')
            ->whereIn('Name_Tractor', $tractorNames)
            ->pluck('Photo_Tractor', 'Name_Tractor');

        $tractors = $tractorNames->map(function ($name) use ($tractorPhotos) {
            $photo = $tractorPhotos->get($name);
            return (object) [
                'name'  => $name,
                'photo' => $photo ? '/iseki_aspro/public/' . $photo : null,
            ];
        });

        return view('perakitan.prosedur.index', compact('tractors'));
    }

    public function show($tractor)
    {
        $listReports = $this->getListReports()->where('Name_Tractor', $tractor);
        
        $asproStorageUrl = '/iseki_aspro/public/storage';
        $pdfs = $listReports->map(function ($p) use ($asproStorageUrl) {
            $relPath = "procedures/{$p->Name_Tractor}/{$p->Name_Area}/{$p->Name_Procedure}.pdf";
            $physPath = "C:/xampp/htdocs/iseki_aspro/public/storage/{$relPath}";
            return (object) [
                'name' => $p->Name_Procedure,
                'area' => $p->Name_Area,
                'tractor' => $p->Name_Tractor,
                'url' => $asproStorageUrl . '/' . $relPath,
                'exists' => file_exists($physPath),
            ];
        })->filter(function ($p) {
            return $p->exists;
        })->values();

        return view('perakitan.prosedur.show', compact('tractor', 'pdfs'));
    }
}
