<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FotoPart;
use App\Models\Marshalling;
use App\Models\Type;
use Illuminate\Http\Request;

class FotoPartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Type::with('mainType')->withCount('marshallings')->with(['marshallings' => function ($q) {
                $q->select('Id_Type', 'Area')->distinct();
            }]);
            return datatables($data)
                ->addIndexColumn()
                ->addColumn('main_type', function ($row) {
                    return $row->mainType ? $row->mainType->Main_Type : '-';
                })
                ->addColumn('location_areas', function ($row) {
                    $areas = $row->marshallings->pluck('Area')->unique()->filter()->values();
                    if ($areas->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }
                    return $areas->map(function ($area) use ($row) {
                        $label = ucwords(str_replace('_', ' ', $area));
                        $url = route('admin.foto-parts.boxes', ['type' => $row->Id_Type, 'area' => $area]);
                        return '<a href="' . $url . '" class="badge bg-light text-dark border me-1 mb-1 text-decoration-none" title="Lihat box di ' . $label . '">' . $label . '</a>';
                    })->implode(' ');
                })
                ->addColumn('list_marshalling', function ($row) {
                    return $row->marshallings_count;
                })
                ->rawColumns(['location_areas'])
                ->make(true);
        }
        return view('admin.foto_parts.index');
    }

    public function boxes($type, $area)
    {
        $type = Type::with('mainType')->findOrFail($type);

        $boxes = \App\Models\Marshalling::where('Id_Type', $type->Id_Type)
            ->where('Area', $area)
            ->select('Box')
            ->distinct()
            ->orderBy('Box')
            ->pluck('Box');

        return view('admin.foto_parts.boxes', [
            'type' => $type,
            'area' => $area,
            'boxes' => $boxes,
        ]);
    }

    public function parts($type, $area, $box)
    {
        $type = Type::with('mainType')->findOrFail($type);

        $marshallings = Marshalling::with('fotoPart')
            ->where('Id_Type', $type->Id_Type)
            ->where('Area', $area)
            ->where('Box', $box)
            ->orderBy('Sequence_No')
            ->get();

        return view('admin.foto_parts.parts', [
            'type' => $type,
            'area' => $area,
            'box' => $box,
            'marshallings' => $marshallings,
        ]);
    }

    public function uploadPhoto(Request $request, $marshalling)
    {
        $request->validate([
            'photo' => 'required|image|max:20480',
        ]);

        $marshalling = Marshalling::with('type')->findOrFail($marshalling);

        $folder = 'part_photos/'
            . $this->sanitizePathSegment($marshalling->type->Type ?? 'unknown-type')
            . '/' . $this->sanitizePathSegment($marshalling->Area)
            . '/' . $this->sanitizePathSegment($marshalling->Box);
        $filename = $this->sanitizePathSegment($marshalling->Code_Rack)
            .'_'. $this->sanitizePathSegment($marshalling->Sequence_No) . '.jpg';
        $relativePath = $folder . '/' . $filename;

        $publicFolder = public_path('uploads/' . $folder);
        if (!is_dir($publicFolder)) {
            mkdir($publicFolder, 0755, true);
        }

        $jpgData = $this->compressToJpg($request->file('photo')->getRealPath());
        file_put_contents(public_path('uploads/' . $relativePath), $jpgData);

        $fotoPart = FotoPart::updateOrCreate(
            ['Id_Marshalling' => $marshalling->Id_Marshalling],
            [
                'Sequence_No' => $marshalling->Sequence_No,
                'Code_Rack' => $marshalling->Code_Rack,
                'Name_Part' => $marshalling->Name_Part,
                'Photo_Path' => 'uploads/' . $relativePath,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Foto part berhasil diupload.',
            'photo_url' => asset($fotoPart->Photo_Path) . '?v=' . time(),
        ]);
    }

    private function sanitizePathSegment($value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $value);
        $value = trim($value, '_');

        return $value !== '' ? $value : 'unknown';
    }

    private function compressToJpg(string $sourcePath, int $maxBytes = 1048576): string
    {
        $image = @imagecreatefromstring(file_get_contents($sourcePath));
        if (!$image) {
            abort(422, 'File yang diupload bukan gambar yang valid.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxDimension = 1600;

        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = min($maxDimension / $width, $maxDimension / $height);
            $width = (int) round($width * $ratio);
            $height = (int) round($height * $ratio);

            $resized = imagecreatetruecolor($width, $height);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
            imagedestroy($image);
            $image = $resized;
        }

        $quality = 90;
        do {
            ob_start();
            imagejpeg($image, null, $quality);
            $data = ob_get_clean();
            $quality -= 10;
        } while (strlen($data) > $maxBytes && $quality >= 30);

        while (strlen($data) > $maxBytes && $width > 400 && $height > 400) {
            $width = (int) round($width * 0.85);
            $height = (int) round($height * 0.85);

            $resized = imagecreatetruecolor($width, $height);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
            imagedestroy($image);
            $image = $resized;

            ob_start();
            imagejpeg($image, null, 75);
            $data = ob_get_clean();
        }

        imagedestroy($image);

        return $data;
    }
}
