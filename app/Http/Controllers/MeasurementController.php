<?php

namespace App\Http\Controllers;

use App\Models\Child;       // WAJIB: Agar controller bisa ambil daftar anak untuk dropdown
use App\Models\Measurement; // WAJIB: Agar controller bisa akses tabel measurements
use App\Models\NutritionStandard;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $childrenQuery = Child::query();
        $measurementsQuery = Measurement::with('child')->orderBy('measurement_date', 'asc');

        if ($user->role === 'kader') {
            $childrenQuery->where('posyandu_id', $user->posyandu_id);
            $measurementsQuery->whereHas('child', function ($query) use ($user) {
                $query->where('posyandu_id', $user->posyandu_id);
            });
        }

        // 1. Ambil anak untuk dropdown sesuai akses user
        $children = $childrenQuery->get();

        // 2. Ambil data timbangan sesuai akses user
        $measurements = $measurementsQuery->get();

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
        // 1. Validasi Input Form (Bawaan + Data Baru Tambahan)
        $request->validate([
            'child_id' => 'required|exists:children,id',
            'weight' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'measurement_date' => 'required|date',
            'head_circumference' => 'nullable|numeric|min:0', // Baru
            'arm_circumference' => 'nullable|numeric|min:0',  // Baru
            'vitamin_a' => 'nullable|string',         // Baru
            'deworming_medicine' => 'required|boolean',        // Baru
            'pmt_status' => 'nullable|string|max:255', // Baru
            'notes' => 'nullable|string',         // Baru
        ]);

        // 2. Ambil Data Anak untuk Menghitung Umur (Bawaan Asli Kamu)
        $child = Child::findOrFail($request->child_id);

        if (Auth::user()->role === 'kader' && (int) $child->posyandu_id !== (int) Auth::user()->posyandu_id) {
            return redirect()->back()
                ->withErrors(['child_id' => 'Anda hanya dapat mencatat anak dari posyandu Anda.'])
                ->withInput();
        }

        // Hitung umur untuk menentukan status berat badan menurut umur (BB/U).
        $birthDate = Carbon::parse($child->birth_date);
        $checkDate = Carbon::parse($request->measurement_date);
        $age = floor($birthDate->diffInMonths($checkDate));

        // Status BB/U memakai satu sumber standar yang sama dengan dashboard.
        $status = $this->determineStatus($child->gender, $age, (float) $request->weight);

        // 4. Simpan Gabungan Data ke Database
        Measurement::create([
            'child_id' => $request->child_id,
            'weight' => $request->weight,
            'height' => $request->height,
            'measurement_date' => $request->measurement_date,
            'status' => $status, // Hasil hitung otomatis kamu

            // Tambahan Kolom Baru:
            'head_circumference' => $request->head_circumference,
            'arm_circumference' => $request->arm_circumference,
            'vitamin_a' => $request->vitamin_a,
            'deworming_medicine' => $request->deworming_medicine,
            'pmt_status' => $request->pmt_status,
            'notes' => $request->notes,
        ]);

        // 5. Kembali ke Halaman Sebelumnya
        return redirect()->back()->with('success', 'Data timbangan berhasil disimpan!');
    }

    public function determineStatus($gender, $age, $weight)
    {
        // Ambil standar dari database berdasarkan umur dan jenis kelamin
        $std = NutritionStandard::where('gender', $gender)
            ->where('age_month', $age)
            ->first();

        if (! $std) {
            return 'Data Standar Tidak Ditemukan';
        }

        if ($weight < $std->min_3sd) {
            return Measurement::STATUS_SEVERE_UNDERWEIGHT;
        } elseif ($weight < $std->min_2sd) {
            return Measurement::STATUS_UNDERWEIGHT;
        } elseif ($weight > $std->plus_1sd) {
            return Measurement::STATUS_OVERWEIGHT_RISK;
        } else {
            return Measurement::STATUS_NORMAL;
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

        if (Auth::user()->role === 'kader' && (int) $child->posyandu_id !== (int) Auth::user()->posyandu_id) {
            abort(403, 'Anda tidak memiliki akses ke data anak ini.');
        }

        $measurements = $child->measurements()->orderBy('measurement_date', 'asc')->get();

        $dataUntukTabel = $measurements->map(function ($m) use ($child) {
            // LOGIKA HITUNG UMUR: (Tanggal Timbang - Tanggal Lahir)
            $birthDate = Carbon::parse($child->birth_date);
            $checkDate = Carbon::parse($m->measurement_date);

            // diffInMonths memberikan selisih bulan secara akurat
            $ageInMonths = floor($birthDate->diffInMonths($checkDate));

            return [
                'formatted_date' => $checkDate->translatedFormat('d F Y'),
                'age' => $ageInMonths.' Bulan', // Hasil hitung otomatis
                'child_name' => $child->name,
                'weight' => $m->weight,
                'height' => $m->height,
                'head_circumference' => $m->head_circumference,
                'arm_circumference' => $m->arm_circumference,
                'vitamin_a' => $m->vitamin_a,
                'deworming_medicine' => $m->deworming_medicine,
                'pmt_status' => $m->pmt_status,
                'status' => $m->status,
            ];
        });

        return response()->json([
            'labels' => $measurements->pluck('measurement_date')->map(fn ($d) => Carbon::parse($d)->format('d/m/y')),
            'weights' => $measurements->pluck('weight'),
            'heights' => $measurements->pluck('height'),
            'measurements' => $dataUntukTabel,
        ]);
    }

    public function exportPDF(Request $request)
    {
        $childId = $request->child_id;
        $chartImage = $request->chart_image; // Ini berisi string Base64

        $child = Child::findOrFail($childId);

        if (Auth::user()->role === 'kader' && (int) $child->posyandu_id !== (int) Auth::user()->posyandu_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan anak ini.');
        }

        $measurements = Measurement::where('child_id', $childId)
            ->orderBy('measurement_date', 'asc')
            ->get();

        $data = [
            'child' => $child,
            'measurements' => $measurements,
            'chartImage' => $chartImage, // Kirim ke view
            'date' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('measurements.pdf', $data);

        return $pdf->download('Laporan_'.$child->name.'.pdf');
    }
}
