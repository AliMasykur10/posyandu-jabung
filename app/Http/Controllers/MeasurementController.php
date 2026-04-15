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

        Measurement::create($request->all());

        return redirect()->back()->with('success', 'Data pengukuran berhasil dicatat!');
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
