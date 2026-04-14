<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Mengambil semua data anak (beserta data ortu & posyandu agar tidak berat/N+1)
        $children = Child::with(['parent', 'posyandu'])->get();

        /// 2. Mengambil data untuk pilihan (dropdown) di form tambah anak
        $parents = ParentDetail::all();
        $posyandus = Posyandu::all();

        // 3. Mengirimkan semua data tersebut ke view
        return view('children.index', compact('children', 'parents', 'posyandus'));
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
        // Validasi input agar data tidak ngawur
        $request->validate([
            'posyandu_id'  => 'required|exists:posyandus,id',
            'parent_id'    => 'required|exists:parent_details,id',
            'name'         => 'required|string|max:255',
            'birth_date'   => 'required|date',
            'gender'       => 'required|in:L,P',
            'birth_weight' => 'required|numeric',
        ]);

        // 2. Simpan ke database
        Child::create($request->all());

        // 3. Kembali ke halaman daftar dengan pesan sukses
        // Kembali ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Data Balita berhasil ditambahkan!');
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
