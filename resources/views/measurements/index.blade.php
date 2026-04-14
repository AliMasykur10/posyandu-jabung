<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Catat Pengukuran (BB & TB)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="mb-6 border-l-4 border-green-500 bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Input Hasil Penimbangan</h3>
                <form action="{{ route('measurements.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pilih Anak</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="child_id"
                                required>
                                <option value="">-- Nama Anak --</option>
                                @foreach ($children as $child)
                                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat Badan (kg)</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="weight"
                                required step="0.01" type="number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tinggi Badan (cm)</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" name="height"
                                required step="0.01" type="number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Timbang</label>
                            <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                name="measurement_date" required type="date" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <button class="mt-4 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700" type="submit">
                        Simpan Pengukuran
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Tanggal</th>
                            <th class="border p-2">Nama Anak</th>
                            <th class="border p-2">Berat (kg)</th>
                            <th class="border p-2">Tinggi (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($measurements as $m)
                            <tr class="text-center">
                                <td class="border p-2">{{ $m->measurement_date }}</td>
                                <td class="border p-2">{{ $m->child->name }}</td>
                                <td class="border p-2">{{ $m->weight }}</td>
                                <td class="border p-2">{{ $m->height }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
