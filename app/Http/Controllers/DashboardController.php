<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\Child;
use App\Models\Measurement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data posyandu yang sudah ada sebelumnya (JANGAN DIHAPUS)
        $all_posyandu = Posyandu::all();

        // 2. Hitung total semua balita yang terdaftar
        $totalAnak = Child::count();

        // 3. Ambil data penimbangan terbaru di bulan berjalan (Juni 2026)
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $pemeriksaanBulanIni = Measurement::whereMonth('measurement_date', $bulanIni)
            ->whereYear('measurement_date', $tahunIni)
            ->get();

        // 4. Hitung jumlah berdasarkan status gizi dari pemeriksaan bulan ini
        // Sesuaikan teks string status gizi ini dengan yang tersimpan di database kamu
        $giziBaik    = $pemeriksaanBulanIni->where('status', 'Gizi Baik (Normal)')->count();
        $giziKurang  = $pemeriksaanBulanIni->where('status', 'Gizi Kurang')->count();
        $giziBuruk   = $pemeriksaanBulanIni->where('status', 'Gizi Buruk')->count();
        $beratLebih  = $pemeriksaanBulanIni->where('status', 'Risiko Berat Lebih')->count();

        // 5. Kirim SEMUA data ke halaman dashboard menggunakan compact
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
