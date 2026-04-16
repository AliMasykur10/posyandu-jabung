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

            <div class="mb-6 border-t-4 border-blue-500 bg-white p-6 shadow-sm sm:rounded-lg">

                <select class="mb-3 mt-1 block w-full rounded-md border-gray-300 px-4 py-2 shadow-sm" id="child-select"
                    name="child_id">
                    <option value="">-- Pilih Anak --</option>
                    @foreach ($children as $child)
                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>

                <h3 class="mb-4 text-lg font-bold text-gray-700">Grafik Perkembangan Berat Badan</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Tanggal</th>
                            <th class="border p-2">Nama Anak</th>
                            <th class="border p-2">Berat (kg)</th>
                            <th class="border p-2">Tinggi (cm)</th>
                            <th>Status Gizi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($measurements as $m)
                            <tr class="text-center">
                                <td class="border p-2">
                                    {{ \Carbon\Carbon::parse($m->measurement_date)->translatedFormat('d F Y') }}
                                </td>
                                <td class="border p-2">{{ $m->child->name }}</td>
                                <td class="border p-2">{{ $m->weight }}</td>
                                <td class="border p-2">{{ $m->height }}</td>
                                <td class="border p-2">
                                    @if ($m->status == 'Gizi Baik (Normal)')
                                        <span
                                            style="background-color: #28a745; color: white; padding: 4px 10px; border-radius: 5px; font-weight: bold; display: inline-block;">
                                            {{ $m->status }}
                                        </span>
                                    @elseif($m->status == 'Gizi Kurang')
                                        <span
                                            style="background-color: #dc3545; color: white; padding: 4px 10px; border-radius: 5px; font-weight: bold; display: inline-block;">
                                            {{ $m->status }}
                                        </span>
                                    @else
                                        <span
                                            style="background-color: #ffc107; color: black; padding: 4px 10px; border-radius: 5px; font-weight: bold; display: inline-block;">
                                            {{ $m->status ?? 'N/A' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    let growthChart; // Kita siapkan variabel kosong untuk menyimpan grafik

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('growthChart').getContext('2d');
        const childSelect = document.getElementById('child-select');

        // 1. Inisialisasi awal (Grafik Kosong)
        growthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Berat Badan (kg)',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    fill: true,
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // 2. Event Listener: Beraksi saat dropdown berubah
        childSelect.addEventListener('change', function() {
            const childId = this.value; // Ambil ID anak yang diklik
            if (!childId) return;

            // 3. Fetch: "Terbang" ke Controller untuk minta data tanpa refresh
            fetch(`/api/measurements/${childId}`)
                .then(response => response.json()) // Ubah kiriman PHP tadi jadi objek JS
                .then(data => {
                    // 4. Update: Masukkan data baru ke dalam grafik yang sudah ada
                    growthChart.data.labels = data.labels;
                    growthChart.data.datasets[0].data = data.weights;
                    growthChart.update(); // Perintah untuk menggambar ulang grafik
                });
        });
    });
</script>
