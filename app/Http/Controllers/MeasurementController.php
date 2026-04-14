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
        // Ambil data pengukuran beserta nama anaknya
        $measurements = Measurement::with('child')->orderBy('measurement_date', 'desc')->get();

        // Ambil daftar anak untuk dropdown di form
        $children = Child::all();

        return view('measurements.index', compact('measurements', 'children'));
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
}
