<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Detail Kesehatan: ') . $child->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
            @php $latest = $child->measurements->last(); @endphp

            <section class="mx-auto mb-8 max-w-5xl overflow-hidden border border-gray-200 bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="font-semibold text-gray-900">Hasil Antropometri Terakhir</h3>
                    <p class="text-sm text-gray-500">{{ $latest ? \Carbon\Carbon::parse($latest->measurement_date)->translatedFormat('d F Y') : 'Belum ada pengukuran' }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['BB/U', $latest?->bb_u_status, $latest?->bb_u_zscore],
                        ['TB/U', $latest?->tb_u_status, $latest?->tb_u_zscore],
                        ['BB/TB', $latest?->bb_tb_status, $latest?->bb_tb_zscore],
                        ['IMT/U', $latest?->imt_u_status, $latest?->imt_u_zscore],
                    ] as [$label, $status, $zScore])
                        <div class="border-b border-gray-200 p-5 sm:border-r lg:border-b-0">
                            <p class="text-xs font-semibold text-gray-500">{{ $label }}</p>
                            <p class="mt-2 font-semibold text-gray-900">{{ $status ?? 'Belum dihitung' }}</p>
                            <p class="mt-1 text-xs text-gray-500">Z-score {{ $zScore ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="mx-auto mb-8 max-w-5xl bg-white p-6 shadow sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Grafik Berat Badan</h3>
                <canvas id="weightChart"></canvas>
            </div>

            <div class="mx-auto max-w-5xl bg-white p-6 shadow sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Grafik Tinggi Badan</h3>
                <canvas id="heightChart"></canvas>
            </div>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($labels) !!};

        // Grafik Berat Badan
        new Chart(document.getElementById('weightChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Berat Badan (kg)',
                    data: {!! json_encode($weights) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true
                }]
            }
        });

        // Grafik Tinggi Badan
        new Chart(document.getElementById('heightChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tinggi Badan (cm)',
                    data: {!! json_encode($heights) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true
                }]
            },

        });
    </script>
</x-app-layout>
