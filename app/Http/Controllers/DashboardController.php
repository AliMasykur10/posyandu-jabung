<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Kita ambil semua data dari tabel posyandus
        $all_posyandu = Posyandu::all();

        // Kita kirim data tersebut ke halaman dashboard
        return view('dashboard', compact('all_posyandu'));
    }
}
