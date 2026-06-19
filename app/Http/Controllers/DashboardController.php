<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\Child;
use App\Models\Measurement;
use App\Models\ParentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     // 1. Data posyandu yang sudah ada sebelumnya (JANGAN DIHAPUS)
    //     $all_posyandu = Posyandu::all();

    //     // 2. Hitung total semua balita yang terdaftar
    //     $totalAnak = Child::count();

    //     // 3. Ambil data penimbangan terbaru di bulan berjalan (Juni 2026)
    //     $bulanIni = now()->month;
    //     $tahunIni = now()->year;

    //     $pemeriksaanBulanIni = Measurement::whereMonth('measurement_date', $bulanIni)
    //         ->whereYear('measurement_date', $tahunIni)
    //         ->get();

    //     // 4. Hitung jumlah berdasarkan status gizi dari pemeriksaan bulan ini
    //     // Sesuaikan teks string status gizi ini dengan yang tersimpan di database kamu
    //     $giziBaik    = $pemeriksaanBulanIni->where('status', 'Gizi Baik (Normal)')->count();
    //     $giziKurang  = $pemeriksaanBulanIni->where('status', 'Gizi Kurang')->count();
    //     $giziBuruk   = $pemeriksaanBulanIni->where('status', 'Gizi Buruk')->count();
    //     $beratLebih  = $pemeriksaanBulanIni->where('status', 'Risiko Berat Lebih')->count();

    //     // 5. Kirim SEMUA data ke halaman dashboard menggunakan compact
    //     return view('dashboard', compact(
    //         'all_posyandu',
    //         'totalAnak',
    //         'giziBaik',
    //         'giziKurang',
    //         'giziBuruk',
    //         'beratLebih'
    //     ));
    // }
    public function index()
    {
        $user = Auth::user();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $measurementQuery = Measurement::with('child')
            ->whereYear('measurement_date', $tahunIni)
            ->whereMonth('measurement_date', $bulanIni);

        $childQuery = Child::query();

        if ($user->role === 'kader') {
            $measurementQuery->whereHas('child', function ($query) use ($user) {
                $query->where('posyandu_id', $user->posyandu_id);
            });

            $childQuery->where('posyandu_id', $user->posyandu_id);
        }

        $pemeriksaanBulanIni = $measurementQuery->get();

        $giziBaik   = $pemeriksaanBulanIni->where('status', 'Gizi Baik (Normal)')->count();
        $giziKurang = $pemeriksaanBulanIni->where('status', 'Gizi Kurang')->count();
        $giziBuruk  = $pemeriksaanBulanIni->where('status', 'Gizi Buruk')->count();
        $beratLebih = $pemeriksaanBulanIni->where('status', 'Risiko Berat Lebih')->count();

        $totalAnak = $childQuery->count();

        // --- ROLE: ADMIN ---
        if ($user->role === 'admin') {
            $all_posyandu = Posyandu::all();

            // Kirim semua variabel ke view admin
            return view('admin.dashboard', compact(
                'all_posyandu',
                'totalAnak',
                'giziBaik',
                'giziKurang',
                'giziBuruk',
                'beratLebih'
            ));
        }

        // --- ROLE: ORANG TUA ---
        if ($user->role === 'orangtua') {
            $parent = ParentDetail::where('user_id', $user->id)->first();
            $myChildren = Child::where('parent_id', $parent->id ?? null)->get();

            return view('parents.dashboard', compact('myChildren'));
        }

        // --- ROLE: KADER / BIDAN ---
        $all_posyandu = $user->role === 'kader'
            ? Posyandu::where('id', $user->posyandu_id)->get()
            : Posyandu::all();

        return view('dashboard', compact(
            'all_posyandu',
            'totalAnak',
            'giziBaik',
            'giziKurang',
            'giziBuruk',
            'beratLebih'
        ));
    }
}
