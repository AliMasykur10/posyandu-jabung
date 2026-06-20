<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Detail Kesehatan: ') . $child->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">

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
