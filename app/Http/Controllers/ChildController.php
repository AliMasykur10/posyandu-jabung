<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil data balita dan nama posyandunya
        $children = Child::with('posyandu')->get();

        // Mengarahkan ke folder resources/views/children/index.blade.php
        return view('children.index', compact('children'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Mengambil semua data posyandu untuk pilihan di dropdown (select)
        $posyandus = Posyandu::all();
        return view('children.create', compact('posyandus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'posyandu_id' => 'required|exists:posyandus,id',
        ]);

        // 2. Simpan ke database
        Child::create($request->all());

        // 3. Kembali ke halaman daftar dengan pesan sukses
        return redirect()->route('children.index')->with('success', 'Child data added successfully!');
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
