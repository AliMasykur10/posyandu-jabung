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

                <div class="mb-4">
                    <form action="{{ route('measurements.pdf') }}" id="pdf-form" method="POST" target="_blank">
                        @csrf
                        <input id="hidden-child-id" name="child_id" type="hidden">
                        <input id="chart-image-input" name="chart_image" type="hidden">

                        <button
                            class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200 hover:bg-red-700"
                            id="btn-cetak-pdf" type="button">
                            Cetak Laporan (PDF)
                        </button>
                    </form>
                </div>

                <h3 class="mb-4 text-lg font-bold text-gray-700">Grafik Pertumbuhan Berat & Tinggi Badan</h3>

                <div class="relative" style="height: 350px;">
                    <canvas id="growthChart"></canvas>
                </div>

            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <table class="block min-w-full border-collapse md:table">
                    <thead>
                        <tr class="border-b bg-gray-100">
                            <th class="p-2 text-center">Tanggal</th>
                            <th class="p-2 text-center">Nama</th>
                            <th class="p-2 text-center">Berat</th>
                            <th class="p-2 text-center">Tinggi</th>
                            <th class="p-2 text-center">Status Gizi</th>
                        </tr>
                    </thead>
                    <tbody id="measurement-table-body">
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
    let growthChart; // Variabel global untuk grafik

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Inisialisasi Chart (Gunakan ID 'growthChart' sesuai elemen <canvas> kamu)
        const ctx = document.getElementById('growthChart').getContext('2d');
        const childSelect = document.getElementById('child-select');
        const pdfBtn = document.getElementById('btn-cetak-pdf');

        growthChart = new Chart(ctx, {
            type: 'line',
            data: { // Kamu tadi lupa membungkus ini dengan kurung kurawal dan properti 'labels'
                labels: [],
                datasets: [ // Data harus di dalam array 'datasets'
                    {
                        label: 'Berat Badan (kg)',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        yAxisID: 'y', // Sumbu kiri
                        tension: 0.3
                    },
                    {
                        label: 'Tinggi Badan (cm)',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        yAxisID: 'y1', // Sumbu kanan
                        tension: 0.3
                    }
                ]
            }, // Tambahkan koma di sini
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Berat (kg)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Tinggi (cm)'
                        },
                        grid: {
                            drawOnChartArea: false // Agar grid tidak tumpang tindih
                        }
                    }
                }
            }
        });

        // 2. Logika saat Dropdown Berubah (Update Chart & Aktifkan Tombol)
        childSelect.addEventListener('change', function() {
            const childId = this.value;

            if (childId) {
                // AKTIFKAN TOMBOL PDF
                pdfBtn.classList.remove('opacity-50', 'pointer-events-none');

                // 1. Ambil data dari server menggunakan fetch
                fetch(`/api/measurements/${childId}`)
                    .then(response => response.json())
                    .then(data => {
                        // --- UPDATE GRAFIK ---
                        growthChart.data.labels = data.labels;
                        growthChart.data.datasets[0].data = data.weights;
                        growthChart.data.datasets[1].data = data.heights;
                        growthChart.update();

                        // --- UPDATE TABEL ---
                        const tableBody = document.getElementById('measurement-table-body');
                        tableBody.innerHTML = ''; // Bersihkan isi tabel lama

                        if (data.measurements.length > 0) {
                            data.measurements.forEach((m) => {
                                // Tentukan warna badge status gizi
                                let badgeClass = 'bg-yellow-500';
                                if (m.status === 'Gizi Baik (Normal)') badgeClass =
                                    'bg-green-600';
                                else if (m.status === 'Gizi Kurang') badgeClass =
                                    'bg-red-600';

                                const row = `
                            <tr class="bg-white border-b">
                                <td class="p-2 text-center text-sm md:text-base">${m.formatted_date}</td>
                                <td class="p-2 text-center text-sm md:text-base">${m.child_name}</td>
                                <td class="p-2 text-center text-sm md:text-base">${m.weight} kg</td>
                                <td class="p-2 text-center text-sm md:text-base">${m.height} cm</td>
                                <td class="p-2 text-center">
                                    <span class="px-2 py-1 rounded text-white text-[10px] md:text-xs font-bold ${badgeClass}">
                                        ${m.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                                tableBody.insertAdjacentHTML('beforeend', row);
                            });
                        } else {
                            tableBody.innerHTML =
                                '<tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada riwayat timbangan untuk anak ini.</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengambil data dari server.');
                    });

            } else {
                // Nonaktifkan tombol jika tidak ada anak yang dipilih
                pdfBtn.classList.add('opacity-50', 'pointer-events-none');
                // Kosongkan tabel dan grafik jika tidak ada pilihan
                document.getElementById('measurement-table-body').innerHTML = '';
                growthChart.data.labels = [];
                growthChart.data.datasets.forEach(ds => ds.data = []);
                growthChart.update();
            }
        });

        // 3. Logika Cetak PDF (Base64)
        pdfBtn.addEventListener('click', function(e) {
            // Karena sekarang pakai Form POST, kita tidak butuh href lagi
            e.preventDefault();

            const childId = childSelect.value;
            if (!childId) {
                alert('Silakan pilih nama anak terlebih dahulu!');
                return;
            }

            // PERBAIKAN: Gunakan ID 'growthChart' (bukan 'myChart')
            const chartCanvas = document.getElementById('growthChart');

            // Konversi ke Base64
            const chartImageBase64 = chartCanvas.toDataURL('image/png');

            // Isi input hidden dan submit form
            document.getElementById('hidden-child-id').value = childId;
            document.getElementById('chart-image-input').value = chartImageBase64;
            document.getElementById('pdf-form').submit();
        });
    });
</script>
