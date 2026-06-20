<?php

namespace App\Http\Controllers;

use App\Models\Child;       // WAJIB: Agar controller bisa ambil daftar anak untuk dropdown
use App\Models\Measurement; // WAJIB: Agar controller bisa akses tabel measurements
use App\Services\AnthropometryCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class MeasurementController extends Controller
{
    public function __construct(private readonly AnthropometryCalculator $calculator) {}

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
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'weight' => 'required|numeric|min:0.9|max:58',
            'height' => 'required|numeric|min:38|max:150',
            'measurement_method' => 'required|in:length,height',
            'measurement_date' => 'required|date|before_or_equal:today',
            'head_circumference' => 'nullable|numeric|min:0', // Baru
            'arm_circumference' => 'nullable|numeric|min:0',  // Baru
            'vitamin_a' => 'nullable|string',         // Baru
            'deworming_medicine' => 'required|boolean',        // Baru
            'pmt_status' => 'nullable|string|max:255', // Baru
            'notes' => 'nullable|string',         // Baru
        ]);

        // 2. Ambil Data Anak untuk Menghitung Umur (Bawaan Asli Kamu)
        $child = Child::findOrFail($validated['child_id']);

        if (Auth::user()->role === 'kader' && (int) $child->posyandu_id !== (int) Auth::user()->posyandu_id) {
            return redirect()->back()
                ->withErrors(['child_id' => 'Anda hanya dapat mencatat anak dari posyandu Anda.'])
                ->withInput();
        }

        try {
            $anthropometry = $this->calculator->calculate(
                $child,
                (float) $validated['weight'],
                (float) $validated['height'],
                $validated['measurement_method'],
                $validated['measurement_date']
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()
                ->withErrors(['anthropometry' => $exception->getMessage()])
                ->withInput();
        }

        // 4. Simpan Gabungan Data ke Database
        Measurement::create(array_merge($anthropometry, [
            'child_id' => $validated['child_id'],
            'weight' => $validated['weight'],
            'height' => $validated['height'],
            'measurement_date' => $validated['measurement_date'],
            'status' => $anthropometry['bb_tb_status'],

            // Tambahan Kolom Baru:
            'head_circumference' => $validated['head_circumference'] ?? null,
            'arm_circumference' => $validated['arm_circumference'] ?? null,
            'vitamin_a' => $validated['vitamin_a'] ?? null,
            'deworming_medicine' => $validated['deworming_medicine'],
            'pmt_status' => $validated['pmt_status'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]));

        // 5. Kembali ke Halaman Sebelumnya
        return redirect()->back()->with('success', 'Data timbangan berhasil disimpan!');
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
                'measurement_method' => $m->measurement_method,
                'head_circumference' => $m->head_circumference,
                'arm_circumference' => $m->arm_circumference,
                'vitamin_a' => $m->vitamin_a,
                'deworming_medicine' => $m->deworming_medicine,
                'pmt_status' => $m->pmt_status,
                'status' => $m->bb_tb_status ?? $m->status,
                'bb_u_status' => $m->bb_u_status,
                'bb_u_zscore' => $m->bb_u_zscore,
                'bb_u_flagged' => $m->bb_u_flagged,
                'tb_u_status' => $m->tb_u_status,
                'tb_u_zscore' => $m->tb_u_zscore,
                'tb_u_flagged' => $m->tb_u_flagged,
                'bb_tb_status' => $m->bb_tb_status,
                'bb_tb_zscore' => $m->bb_tb_zscore,
                'bb_tb_flagged' => $m->bb_tb_flagged,
                'imt_u_status' => $m->imt_u_status,
                'imt_u_zscore' => $m->imt_u_zscore,
                'imt_u_flagged' => $m->imt_u_flagged,
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
