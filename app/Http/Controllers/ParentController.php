<?php

namespace App\Http\Controllers;

use App\Models\ParentDetail;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user();

        // Cek Role User yang sedang login
        if ($user->role === 'kader') {
            // 1. KADER: Hanya mengambil data keluarga di posyandunya sendiri
            $parents = ParentDetail::with('posyandu')
                ->where('posyandu_id', $user->posyandu_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif (in_array($user->role, ['admin', 'puskesmas', 'kepala_desa', 'bidan'])) {
            // 2. ADMIN, PUSKESMAS, KEPALA DESA, BIDAN: Mengambil seluruh data dari 5 posyandu
            $parents = ParentDetail::with('posyandu')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Jika ada role lain (misal orang tua) yang kesasar masuk ke halaman ini, blokir aksenya
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('parents.index', compact('parents'));
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
        // 1. Validasi ketat sesuai standar Kemenkes RI
        $request->validate([
            'no_kk'        => 'required|string|size:16|unique:parent_details,no_kk', // Validasi unik anti-duplikasi KK
            'nik_mother'   => 'nullable|string|size:16',
            'nik_father'   => 'nullable|string|size:16',
            'mother_name'  => 'required|string|max:255',
            'father_name'  => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'address'      => 'required|string',
            'rt'           => 'required|string|max:3',
            'rw'           => 'required|string|max:3',
            'posyandu_id'  => 'nullable|exists:posyandus,id',
        ], [
            // Custom pesan error jika diperlukan agar kader tidak bingung
            'no_kk.unique' => 'Nomor Kartu Keluarga (KK) ini sudah terdaftar di sistem.',
            'no_kk.size'   => 'Nomor Kartu Keluarga (KK) harus tepat 16 digit.',
        ]);

        if (Auth::user()->role === 'kader' && is_null(Auth::user()->posyandu_id)) {
            return redirect()->back()
                ->withErrors(['posyandu_id' => 'Akun kader belum terhubung ke posyandu.'])
                ->withInput();
        }

        // 2. Ambil posyandu_id secara otomatis dari akun Kader yang sedang login
        $posyanduId = Auth::user()->posyandu_id;

        if (is_null($posyanduId)) {
            $posyanduId = $request->posyandu_id;
        }

        if (is_null($posyanduId)) {
            return redirect()->back()->withErrors(['posyandu_id' => 'Posyandu wajib dipilih oleh Admin.']);
        }

        DB::transaction(function () use ($request, $posyanduId) {

            // Buat User (Akun Login Orang Tua)
            $user = User::create([
                'name'        => $request->mother_name,
                'email'       => $request->no_kk . '@posyandu.id', // Login menggunakan No KK @posyandu.id
                'password'    => Hash::make('12345678'),           // Password default
                'role'        => 'orangtua',
                'posyandu_id' => $posyanduId,
            ]);

            // Simpan Data Orang Tua
            ParentDetail::create([
                'user_id'      => $user->id, // Menghubungkan ke user yang baru dibuat
                'posyandu_id'  => $posyanduId,
                'no_kk'        => $request->no_kk,
                'nik_mother'   => $request->nik_mother,
                'nik_father'   => $request->nik_father,
                'mother_name'  => $request->mother_name,
                'father_name'  => $request->father_name,
                'phone_number' => $request->phone_number,
                'address'      => $request->address,
                'rt'           => sprintf('%03d', $request->rt),
                'rw'           => sprintf('%03d', $request->rw),
            ]);
        });

        return redirect()->back()->with('success', 'Data Keluarga dan Akun Orang Tua berhasil ditambahkan!');
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
    public function edit(ParentDetail $parent)
    {
        $this->ensureCanManage($parent);

        $posyandus = Auth::user()->role === 'admin'
            ? Posyandu::orderBy('name')->get()
            : Posyandu::whereKey(Auth::user()->posyandu_id)->get();

        return view('parents.edit', compact('parent', 'posyandus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentDetail $parent)
    {
        $this->ensureCanManage($parent);

        $validated = $request->validate([
            'no_kk'        => [
                'required',
                'string',
                'size:16',
                Rule::unique('parent_details', 'no_kk')->ignore($parent->id),
            ],
            'nik_mother'   => 'nullable|string|size:16',
            'nik_father'   => 'nullable|string|size:16',
            'mother_name'  => 'required|string|max:255',
            'father_name'  => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'address'      => 'required|string',
            'rt'           => 'required|integer|min:1|max:999',
            'rw'           => 'required|integer|min:1|max:999',
            'posyandu_id'  => 'nullable|exists:posyandus,id',
        ], [
            'no_kk.unique' => 'Nomor Kartu Keluarga (KK) ini sudah terdaftar di sistem.',
            'no_kk.size'   => 'Nomor Kartu Keluarga (KK) harus tepat 16 digit.',
        ]);

        $posyanduId = Auth::user()->role === 'kader'
            ? Auth::user()->posyandu_id
            : $validated['posyandu_id'];

        if (is_null($posyanduId)) {
            return redirect()->back()
                ->withErrors(['posyandu_id' => 'Posyandu wajib dipilih.'])
                ->withInput();
        }

        $parentEmail = $validated['no_kk'] . '@posyandu.id';
        $emailUsed = User::where('email', $parentEmail)
            ->when($parent->user_id, fn($query) => $query->where('id', '!=', $parent->user_id))
            ->exists();

        if ($emailUsed) {
            return redirect()->back()
                ->withErrors(['no_kk' => 'Nomor KK tersebut sudah digunakan oleh akun lain.'])
                ->withInput();
        }

        DB::transaction(function () use ($parent, $validated, $posyanduId, $parentEmail) {
            $parent->update([
                'posyandu_id'  => $posyanduId,
                'no_kk'        => $validated['no_kk'],
                'nik_mother'   => $validated['nik_mother'],
                'nik_father'   => $validated['nik_father'],
                'mother_name'  => $validated['mother_name'],
                'father_name'  => $validated['father_name'],
                'phone_number' => $validated['phone_number'],
                'address'      => $validated['address'],
                'rt'           => sprintf('%03d', $validated['rt']),
                'rw'           => sprintf('%03d', $validated['rw']),
            ]);

            $parent->children()->update(['posyandu_id' => $posyanduId]);

            if ($parent->user) {
                $parent->user->update([
                    'name'        => $validated['mother_name'],
                    'email'       => $parentEmail,
                    'posyandu_id' => $posyanduId,
                ]);
            }
        });

        return redirect()->route('parents.index')
            ->with('success', 'Data keluarga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentDetail $parent)
    {
        $this->ensureCanManage($parent);

        DB::transaction(function () use ($parent) {
            $user = $parent->user;

            $parent->delete();

            if ($user && $user->role === 'orangtua') {
                $user->delete();
            }
        });

        return redirect()->route('parents.index')
            ->with('success', 'Data keluarga, akun orang tua, dan data anak terkait berhasil dihapus.');
    }

    private function ensureCanManage(ParentDetail $parent): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if (
            $user->role === 'kader'
            && !is_null($user->posyandu_id)
            && (int) $parent->posyandu_id === (int) $user->posyandu_id
        ) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke data keluarga ini.');
    }
}
