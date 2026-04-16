<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Data Balita (Children)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="mb-6 bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold text-blue-600">Tambah Data Balita Baru</h3>
                <form action="{{ route('children.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap Anak</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="name"
                                required type="text">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Ibu (Orang Tua)</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="parent_id"
                                required>
                                <option value="">-- Pilih Orang Tua --</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->mother_name }} (Ibu)</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi Posyandu</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="posyandu_id"
                                required>
                                <option value="">-- Pilih Posyandu --</option>
                                @foreach ($posyandus as $posyandu)
                                    <option value="{{ $posyandu->id }}">{{ $posyandu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="birth_date"
                                required type="date">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="gender"
                                required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat Lahir (kg)</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="birth_weight"
                                required step="0.01" type="number">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="rounded bg-blue-600 px-4 py-2 font-bold text-white shadow hover:bg-blue-700"
                            type="submit">
                            Simpan Data Balita
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Daftar Balita Terdaftar</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Nama Balita</th>
                                <th class="border p-2">Nama Ibu</th>
                                <th class="border p-2">Posyandu</th>
                                <th class="border p-2">Tgl Lahir</th>
                                <th class="border p-2">JK</th>
                                <th class="border p-2">Berat Lahir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($children as $child)
                                <tr class="text-center hover:bg-gray-50">
                                    <td class="border p-2 text-left font-semibold">{{ $child->name }}</td>
                                    <td class="border p-2">{{ $child->parent->mother_name }}</td>
                                    <td class="border p-2">{{ $child->posyandu->name }}</td>
                                    <td class="border p-2">{{ $child->birth_date }}</td>
                                    <td class="border p-2">{{ $child->gender }}</td>
                                    <td class="border p-2">{{ $child->birth_weight }} kg</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
