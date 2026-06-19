<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow sm:rounded-lg border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500">Total Balita Terdaftar</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $totalAnak }} Anak</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow sm:rounded-lg border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500">Gizi Baik (Bulan Ini)</div>
                    <div class="text-2xl font-bold text-green-600">{{ $giziBaik }} Anak</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="text-sm font-medium text-gray-500">Gizi Kurang (Bulan Ini)</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $giziKurang }} Anak</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow sm:rounded-lg border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-500">Gizi Buruk / Stunting</div>
                    <div class="text-2xl font-bold text-red-600">{{ $giziBuruk }} Anak</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow sm:rounded-lg md:col-span-1 flex flex-col items-center justify-center">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 text-center">Persentase Status Gizi Desa</h3>
                    <div style="width: 200px; height: 200px;">
                        <canvas id="giziDonutChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-bold text-blue-600">Posyandu Units - Jabung Sisir</h3>

                    <table class="min-w-full border-collapse border border-gray-300 shadow-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-16 border border-gray-300 px-4 py-2 text-center">No</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Posyandu Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Address</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($all_posyandu as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-2 font-medium">{{ $item->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-gray-600">{{ $item->address ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-center text-red-500"
                                        colspan="3">
                                        No data found. Please run the seeder!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('giziDonutChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Gizi Baik', 'Gizi Kurang', 'Gizi Buruk', 'Risiko Lebih'],
                    datasets: [{
                        data: [
                            {{ $giziBaik }}, 
                            {{ $giziKurang }}, 
                            {{ $giziBuruk }}, 
                            {{ $beratLebih }}
                        ],
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>