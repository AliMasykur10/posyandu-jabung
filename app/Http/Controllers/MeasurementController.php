<?php

namespace App\Http\Controllers;

use App\Models\Measurement; // WAJIB: Agar controller bisa akses tabel measurements
use App\Models\Child;       // WAJIB: Agar controller bisa ambil daftar anak untuk dropdown
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
            'child_id' => 'required|exists:children,id',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'measurement_date' => 'required|date',
        ]);

        $child = Child::find($request->child_id);

        // 1. Hitung Umur
        $tglLahir = \Carbon\Carbon::parse($child->date_of_birth);
        $tglUkur = \Carbon\Carbon::parse($request->measurement_date);
        $umurBulan = $tglLahir->diffInMonths($tglUkur);

        // 2. Panggil Fungsi (TAMBAHKAN $child->gender sebagai parameter ketiga)
        $status = $this->hitungStatusGizi($umurBulan, $request->weight, $child->gender);

        // 3. Simpan
        Measurement::create([
            'child_id' => $request->child_id,
            'weight' => $request->weight,
            'height' => $request->height,
            'measurement_date' => $request->measurement_date,
            'status' => $status,
        ]);

        return redirect()->back()->with('success', "Data dicatat! Status: $status");
    }

    // FUNGSI LOGIKA (The Brain)
    private function hitungStatusGizi($umur, $berat, $gender)
    {
        // Standar Berat Badan menurut Umur (BB/U) - WHO/Kemenkes
        // Satuan: Kilogram (kg)
        $standar = [
            'L' => [ // Laki-laki
                0 => ['min' => 2.5, 'max' => 4.4],
                1 => ['min' => 3.4, 'max' => 5.8],
                2 => ['min' => 4.3, 'max' => 7.1],
                3 => ['min' => 5.0, 'max' => 8.0],
                4 => ['min' => 5.6, 'max' => 8.7],
                5 => ['min' => 6.0, 'max' => 9.3],
                6 => ['min' => 6.4, 'max' => 9.8],
                7 => ['min' => 6.7, 'max' => 10.3],
                8 => ['min' => 6.9, 'max' => 10.7],
                9 => ['min' => 7.1, 'max' => 11.0],
                10 => ['min' => 7.4, 'max' => 11.4],
                11 => ['min' => 7.6, 'max' => 11.7],
                12 => ['min' => 7.7, 'max' => 12.0],
            ],
            'P' => [ // Perempuan
                0 => ['min' => 2.4, 'max' => 4.2],
                1 => ['min' => 3.2, 'max' => 5.4],
                2 => ['min' => 3.9, 'max' => 6.6],
                3 => ['min' => 4.5, 'max' => 7.5],
                4 => ['min' => 5.0, 'max' => 8.2],
                5 => ['min' => 5.4, 'max' => 8.8],
                6 => ['min' => 5.7, 'max' => 9.3],
                7 => ['min' => 6.0, 'max' => 9.8],
                8 => ['min' => 6.3, 'max' => 10.2],
                9 => ['min' => 6.5, 'max' => 10.5],
                10 => ['min' => 6.7, 'max' => 10.9],
                11 => ['min' => 6.9, 'max' => 11.2],
                12 => ['min' => 7.0, 'max' => 11.5],
            ]
        ];

        // Ambil referensi berdasarkan gender dan umur
        if (isset($standar[$gender][$umur])) {
            $ref = $standar[$gender][$umur];
            if ($berat < $ref['min']) return 'Gizi Kurang';
            if ($berat > $ref['max']) return 'Gizi Lebih';
            return 'Gizi Baik (Normal)';
        }

        return 'Data Standar Belum Tersedia (0-12 bln)';
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
        // Mengambil data timbangan berdasarkan ID anak yang dipilih
        $data = Measurement::where('child_id', $child_id)
            ->orderBy('measurement_date', 'asc')
            ->get();

        // Mengembalikan data dalam format JSON agar bisa dibaca JavaScript
        return response()->json([
            'labels' => $data->pluck('measurement_date'),
            'weights' => $data->pluck('weight'),
        ]);
    }
}
