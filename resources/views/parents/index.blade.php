<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Data Keluarga (Parents)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-bold text-blue-600">Tambah Data Keluarga Baru</h3>

                @if (session('success'))
                    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('parents.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">No. Kartu Keluarga (KK)
                                    <span class="text-red-500">*</span></label>
                                <input
                                    class="@error('no_kk') border-red-500 @enderror w-full rounded border p-2 focus:ring-blue-500"
                                    maxlength="16" name="no_kk" placeholder="16 Digit Nomor KK" required
                                    type="text" value="{{ old('no_kk') }}">
                            </div>
                            
                            @if (is_null(auth()->user()->posyandu_id))
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Pilih Posyandu <span
                                            class="text-red-500">*</span></label>
                                    <select class="w-full rounded border p-2 focus:ring-blue-500" name="posyandu_id"
                                        required>
                                        <option value="">-- Pilih Posyandu Tujuan --</option>
                                        @foreach (\App\Models\Posyandu::all() as $posyandu)
                                            <option {{ old('posyandu_id') == $posyandu->id ? 'selected' : '' }}
                                                value="{{ $posyandu->id }}">
                                                {{ $posyandu->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('posyandu_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Ibu <span
                                        class="text-red-500">*</span></label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="mother_name"
                                    placeholder="Nama Lengkap Ibu" required type="text"
                                    value="{{ old('mother_name') }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">NIK Ibu (Opsional)</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" maxlength="16"
                                    name="nik_mother" placeholder="16 Digit NIK Ibu" type="text"
                                    value="{{ old('nik_mother') }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">No. Telepon / WA</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="phone_number"
                                    placeholder="Contoh: 081234567xxx" type="text"
                                    value="{{ old('phone_number') }}">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Ayah (Opsional)</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="father_name"
                                    placeholder="Nama Lengkap Ayah" type="text" value="{{ old('father_name') }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">NIK Ayah (Opsional)</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" maxlength="16"
                                    name="nik_father" placeholder="16 Digit NIK Ayah" type="text"
                                    value="{{ old('nik_father') }}">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Alamat Rumah <span
                                        class="text-red-500">*</span></label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="address"
                                    placeholder="Nama Jalan / RT / RW / Dusun" required type="text"
                                    value="{{ old('address') }}">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">RT <span
                                            class="text-red-500">*</span></label>
                                    <input class="w-full rounded border p-2 focus:ring-blue-500" max="999"
                                        min="1" name="rt" placeholder="001" required type="number"
                                        value="{{ old('rt') }}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">RW <span
                                            class="text-red-500">*</span></label>
                                    <input class="w-full rounded border p-2 focus:ring-blue-500" max="999"
                                        min="1" name="rw" placeholder="002" required type="number"
                                        value="{{ old('rw') }}">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-6">
                        <button class="rounded bg-blue-600 px-4 py-2 font-semibold text-white shadow hover:bg-blue-700"
                            type="submit">
                            Simpan Data Keluarga
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-bold text-gray-700">Daftar Data Keluarga</h3>

                <table class="w-full border-collapse border text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-3">No</th>
                            <th class="border p-3">No. KK / NIK Ibu</th>
                            <th class="border p-3">Nama Ibu / Ayah</th>
                            <th class="border p-3">Posyandu</th>
                            <th class="border p-3">Kontak & Alamat</th>
                            <th class="border p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $index => $parent)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-3 text-center">{{ $index + 1 }}</td>
                                <td class="border p-3">
                                    <div class="font-bold text-gray-800">{{ $parent->no_kk }}</div>
                                    <div class="text-xs text-gray-500">NIK: {{ $parent->nik_mother ?? '-' }}</div>
                                </td>
                                <td class="border p-3">
                                    <div class="font-semibold text-blue-600">{{ $parent->mother_name }}</div>
                                    <div class="text-xs text-gray-600">Suami: {{ $parent->father_name ?? '-' }}</div>
                                </td>
                                <td class="border p-3">
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                        {{ $parent->posyandu->name ?? 'Tidak Terikat' }}
                                    </span>
                                </td>
                                <td class="border p-3">
                                    <div class="text-xs font-medium">{{ $parent->phone_number ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{ $parent->address }} (RT: {{ $parent->rt }} / RW: {{ $parent->rw }})
                                    </div>
                                </td>
                                <td class="border p-3 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a class="text-xs font-semibold text-blue-600 hover:underline"
                                            href="{{ route('parents.edit', $parent) }}">
                                            Edit
                                        </a>

                                        <form action="{{ route('parents.destroy', $parent) }}" method="POST"
                                            onsubmit="return confirm('Hapus keluarga {{ addslashes($parent->mother_name) }}? Data anak dan seluruh riwayat pengukurannya juga akan terhapus.');">
                                            @csrf
                                            @method('DELETE')

                                            <button class="text-xs font-semibold text-red-600 hover:underline"
                                                type="submit">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border p-4 text-center text-gray-500" colspan="6">Belum ada data
                                    keluarga terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
