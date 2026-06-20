<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Data Keluarga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-blue-600">Perbarui Data Keluarga</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Perubahan nama dan nomor KK juga akan memperbarui akun login orang tua.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('parents.update', $parent) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    No. Kartu Keluarga (KK) <span class="text-red-500">*</span>
                                </label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" maxlength="16"
                                    name="no_kk" required type="text"
                                    value="{{ old('no_kk', $parent->no_kk) }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    Posyandu <span class="text-red-500">*</span>
                                </label>

                                @if (auth()->user()->role === 'admin')
                                    <select class="w-full rounded border p-2 focus:ring-blue-500" name="posyandu_id"
                                        required>
                                        <option value="">-- Pilih Posyandu --</option>
                                        @foreach ($posyandus as $posyandu)
                                            <option
                                                {{ (int) old('posyandu_id', $parent->posyandu_id) === (int) $posyandu->id ? 'selected' : '' }}
                                                value="{{ $posyandu->id }}">
                                                {{ $posyandu->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input name="posyandu_id" type="hidden" value="{{ $parent->posyandu_id }}">
                                    <input class="w-full rounded border bg-gray-100 p-2 text-gray-600" readonly
                                        type="text" value="{{ $parent->posyandu->name ?? '-' }}">
                                @endif
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    Nama Ibu <span class="text-red-500">*</span>
                                </label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="mother_name"
                                    required type="text" value="{{ old('mother_name', $parent->mother_name) }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">NIK Ibu</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" maxlength="16"
                                    name="nik_mother" type="text"
                                    value="{{ old('nik_mother', $parent->nik_mother) }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">No. Telepon / WA</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="phone_number"
                                    type="text" value="{{ old('phone_number', $parent->phone_number) }}">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Ayah</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" name="father_name"
                                    type="text" value="{{ old('father_name', $parent->father_name) }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">NIK Ayah</label>
                                <input class="w-full rounded border p-2 focus:ring-blue-500" maxlength="16"
                                    name="nik_father" type="text"
                                    value="{{ old('nik_father', $parent->nik_father) }}">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    Alamat Rumah <span class="text-red-500">*</span>
                                </label>
                                <textarea class="w-full rounded border p-2 focus:ring-blue-500" name="address" required
                                    rows="3">{{ old('address', $parent->address) }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        RT <span class="text-red-500">*</span>
                                    </label>
                                    <input class="w-full rounded border p-2 focus:ring-blue-500" max="999"
                                        min="1" name="rt" required type="number"
                                        value="{{ old('rt', (int) $parent->rt) }}">
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        RW <span class="text-red-500">*</span>
                                    </label>
                                    <input class="w-full rounded border p-2 focus:ring-blue-500" max="999"
                                        min="1" name="rw" required type="number"
                                        value="{{ old('rw', (int) $parent->rw) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700"
                            type="submit">
                            Simpan Perubahan
                        </button>

                        <a class="rounded border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                            href="{{ route('parents.index') }}">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
