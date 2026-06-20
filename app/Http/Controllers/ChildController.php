<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Mengambil semua data anak (beserta data ortu & posyandu agar tidak berat/N+1)
        $query = Child::with(['parent', 'posyandu']);

        if (auth()->user()->role === 'kader') {
            $query->where('posyandu_id', auth()->user()->posyandu_id);
        }

        $children = $query->get();

        if (auth()->user()->role === 'admin') {
            $parents = ParentDetail::all();
            $posyandus = Posyandu::all();
        } else {
            $parents = ParentDetail::where('posyandu_id', auth()->user()->posyandu_id)->get();
            $posyandus = Posyandu::where('id', auth()->user()->posyandu_id)->get();
        }

        // 3. Mengirimkan semua data tersebut ke view
        return view('children.index', compact('children', 'parents', 'posyandus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'posyandu_id'  => 'required|exists:posyandus,id',
            'parent_id'    => 'required|exists:parent_details,id',
            'name'         => 'required|string|max:255',
            'birth_date'   => 'required|date',
            'gender'       => 'required|in:L,P',
            'birth_weight' => 'required|numeric',
        ]);

        if (Auth::user()->role === 'kader') {
            $validated['posyandu_id'] = Auth::user()->posyandu_id;
        }

        $parent = ParentDetail::findOrFail($validated['parent_id']);

        if ((int) $parent->posyandu_id !== (int) $validated['posyandu_id']) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'Orang tua harus berada di posyandu yang sama dengan balita.'])
                ->withInput();
        }

        Child::create($validated);

        // 3. Kembali ke halaman daftar dengan pesan sukses
        // Kembali ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Data Balita berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Child $child)
    {
        // Ambil relasi measurements
        $child->load(['measurements' => function ($query) {
            $query->orderBy('measurement_date', 'asc');
        }]);

        // Keamanan: Jika yang login adalah orang tua,
        // pastikan hanya melihat anaknya sendiri
        if (Auth::user()->role === 'orangtua') {

            $parent = ParentDetail::where('user_id', Auth::id())->first();

            if (!$parent || $child->parent_id !== $parent->id) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke data anak ini.');
            }
        }

        if (Auth::user()->role === 'kader' && (int) $child->posyandu_id !== (int) Auth::user()->posyandu_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke data anak ini.');
        }

        // Data grafik
        $labels = $child->measurements
            ->pluck('measurement_date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('d M Y');
            });

        // Data berat badan (untuk grafik 1)
        $weights = $child->measurements->pluck('weight');

        // Data tinggi badan (untuk grafik 2 - ini yang baru ditambahkan)
        $heights = $child->measurements->pluck('height');

        // Kirim semua data ke view
        return view('children.show', compact('child', 'labels', 'weights', 'heights'));
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
