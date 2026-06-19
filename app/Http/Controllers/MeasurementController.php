<?php

namespace App\Http\Controllers;

use App\Models\Child;       // WAJIB: Agar controller bisa ambil daftar anak untuk dropdown
use App\Models\Measurement; // WAJIB: Agar controller bisa akses tabel measurements
use App\Models\NutritionStandard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Ambil semua anak untuk dropdown
        $children = Child::all();

        // 2. Ambil semua data timbangan, urutkan dari yang terlama ke terbaru agar grafiknya benar
        $measurements = Measurement::with('child')->orderBy('measurement_date', 'asc')->get();

        // 3. Siapkan data untuk grafik (Label tanggal dan Angka Berat)
        $labels = $measurements->pluck('measurement_date');
        $weights = $measurements->pluck('weight');

        return view('measurements.index', compact('children', 'measurements', 'labels', 'weights'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'child_id' => 'required',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'measurement_date' => 'required|date',
        ]);

        $child = Child::find($request->child_id);

        // Hitung Umur untuk Logika Status Gizi
        $birthDate = \Carbon\Carbon::parse($child->birth_date);
        $checkDate = \Carbon\Carbon::parse($request->measurement_date);
        $age = floor($birthDate->diffInMonths($checkDate));

        // LOGIKA SEDERHANA STATUS GIZI (Contoh)
        // Kamu bisa mengganti ini dengan tabel standar antropometri nanti
        $status = 'Gizi Baik (Normal)';
        if ($age <= 6 && $request->weight < 3.0) {
            $status = 'Gizi Kurang';
        } elseif ($request->weight < 2.5) {
            $status = 'Gizi Buruk';
        }

        Measurement::create([
            'child_id' => $request->child_id,
            'weight' => $request->weight,
            'height' => $request->height,
            'measurement_date' => $request->measurement_date,
            'status' => $status, // Terisi otomatis oleh sistem
        ]);

        return redirect()->back()->with('success', 'Data timbangan berhasil disimpan!');
    }

    // // FUNGSI LOGIKA (The Brain)
    // private function hitungStatusGizi($umur, $berat, $gender)
    // {
    //     // Standar Berat Badan menurut Umur (BB/U) - WHO/Kemenkes
    //     // Satuan: Kilogram (kg)
    //     $standar = [
    //         'L' => [ // Laki-laki
    //             0 => ['min' => 2.5, 'max' => 4.4],
    //             1 => ['min' => 3.4, 'max' => 5.8],
    //             2 => ['min' => 4.3, 'max' => 7.1],
    //             3 => ['min' => 5.0, 'max' => 8.0],
    //             4 => ['min' => 5.6, 'max' => 8.7],
    //             5 => ['min' => 6.0, 'max' => 9.3],
    //             6 => ['min' => 6.4, 'max' => 9.8],
    //             7 => ['min' => 6.7, 'max' => 10.3],
    //             8 => ['min' => 6.9, 'max' => 10.7],
    //             9 => ['min' => 7.1, 'max' => 11.0],
    //             10 => ['min' => 7.4, 'max' => 11.4],
    //             11 => ['min' => 7.6, 'max' => 11.7],
    //             12 => ['min' => 7.7, 'max' => 12.0],
    //         ],
    //         'P' => [ // Perempuan
    //             0 => ['min' => 2.4, 'max' => 4.2],
    //             1 => ['min' => 3.2, 'max' => 5.4],
    //             2 => ['min' => 3.9, 'max' => 6.6],
    //             3 => ['min' => 4.5, 'max' => 7.5],
    //             4 => ['min' => 5.0, 'max' => 8.2],
    //             5 => ['min' => 5.4, 'max' => 8.8],
    //             6 => ['min' => 5.7, 'max' => 9.3],
    //             7 => ['min' => 6.0, 'max' => 9.8],
    //             8 => ['min' => 6.3, 'max' => 10.2],
    //             9 => ['min' => 6.5, 'max' => 10.5],
    //             10 => ['min' => 6.7, 'max' => 10.9],
    //             11 => ['min' => 6.9, 'max' => 11.2],
    //             12 => ['min' => 7.0, 'max' => 11.5],
    //         ]
    //     ];

    //     // Ambil referensi berdasarkan gender dan umur
    //     if (isset($standar[$gender][$umur])) {
    //         $ref = $standar[$gender][$umur];
    //         if ($berat < $ref['min']) return 'Gizi Kurang';
    //         if ($berat > $ref['max']) return 'Gizi Lebih';
    //         return 'Gizi Baik (Normal)';
    //     }

    //     return 'Data Standar Belum Tersedia (0-12 bln)';
    // }
    public function determineStatus($gender, $age, $weight)
    {
        // Ambil standar dari database berdasarkan umur dan jenis kelamin
        $std = NutritionStandard::where('gender', $gender)
            ->where('age_month', $age)
            ->first();

        if (!$std) return 'Data Standar Tidak Ditemukan';

        if ($weight < $std->min_3sd) {
            return 'Gizi Buruk (Severely Underweight)';
        } elseif ($weight < $std->min_2sd) {
            return 'Gizi Kurang (Underweight)';
        } elseif ($weight > $std->plus_1sd) {
            return 'Risiko Berat Badan Lebih';
        } else {
            return 'Gizi Baik (Normal)';
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getChartData($child_id)
    {
        // Ambil data anak beserta riwayat timbangannya
        $child = Child::with('measurements')->findOrFail($child_id);

        $measurements = $child->measurements()->orderBy('measurement_date', 'asc')->get();

        $dataUntukTabel = $measurements->map(function ($m) use ($child) {
            // LOGIKA HITUNG UMUR: (Tanggal Timbang - Tanggal Lahir)
            $birthDate = \Carbon\Carbon::parse($child->birth_date);
            $checkDate = \Carbon\Carbon::parse($m->measurement_date);

            // diffInMonths memberikan selisih bulan secara akurat
            $ageInMonths = floor($birthDate->diffInMonths($checkDate));

            return [
                'formatted_date' => $checkDate->translatedFormat('d F Y'),
                'age'            => $ageInMonths . ' Bulan', // Hasil hitung otomatis
                'child_name'     => $child->name,
                'weight'         => $m->weight,
                'height'         => $m->height,
                'status'         => $m->status
            ];
        });

        return response()->json([
            'labels'       => $measurements->pluck('measurement_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/y')),
            'weights'      => $measurements->pluck('weight'),
            'heights'      => $measurements->pluck('height'),
            'measurements' => $dataUntukTabel
        ]);
    }

    public function exportPDF(Request $request)
    {
        $childId = $request->child_id;
        $chartImage = $request->chart_image; // Ini berisi string Base64

        $child = Child::findOrFail($childId);
        $measurements = Measurement::where('child_id', $childId)
            ->orderBy('measurement_date', 'asc')
            ->get();

        $data = [
            'child' => $child,
            'measurements' => $measurements,
            'chartImage' => $chartImage, // Kirim ke view
            'date' => now()->format('d/m/Y')
        ];

        $pdf = Pdf::loadView('measurements.pdf', $data);
        return $pdf->download('Laporan_' . $child->name . '.pdf');
    }
}
