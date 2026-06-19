<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Dashboard Admin</h2>
                <p class="text-sm text-gray-500">Pemantauan seluruh Posyandu pada {{ $periodLabel }}</p>
            </div>
            <span class="text-sm font-medium text-gray-600">Cakupan {{ $coveragePercentage }}%</span>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 px-4 sm:grid-cols-2 sm:px-0 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Posyandu</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalPosyandu }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Total Kader</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalKader }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Keluarga Terdaftar</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalFamilies }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Balita Terdaftar</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalChildren }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $measuredChildren }} ditimbang bulan ini</p>
                </div>
            </section>

            <section class="border-y border-gray-200 bg-white px-4 py-5 sm:rounded-lg sm:border sm:px-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Cakupan Penimbangan {{ $periodLabel }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $measuredChildren }} dari {{ $totalChildren }} balita sudah memiliki pengukuran bulan ini.
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-2xl font-semibold text-blue-700">{{ $coveragePercentage }}%</span>
                        <p class="text-xs text-gray-500">{{ $unmeasuredCount }} belum ditimbang</p>
                    </div>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded bg-gray-200">
                    <div class="h-full bg-blue-600" style="width: {{ min($coveragePercentage, 100) }}%"></div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 px-4 sm:px-0 lg:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="font-semibold text-gray-900">Status Gizi Bulan Ini</h3>
                    <p class="mb-4 text-sm text-gray-500">Pengukuran terbaru dari setiap balita</p>
                    <div class="mx-auto h-72 max-w-sm"><canvas id="statusChart"></canvas></div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-3">
                    <h3 class="font-semibold text-gray-900">Tren Enam Bulan</h3>
                    <p class="mb-4 text-sm text-gray-500">Jumlah balita per status gizi setiap bulan</p>
                    <div class="h-72"><canvas id="trendChart"></canvas></div>
                </div>
            </section>

            <section class="overflow-hidden border-y border-gray-200 bg-white sm:rounded-lg sm:border">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <h3 class="font-semibold text-gray-900">Perbandingan Posyandu</h3>
                    <p class="text-sm text-gray-500">Cakupan dan balita berisiko pada {{ $periodLabel }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Posyandu</th>
                                <th class="px-6 py-3 text-center">Keluarga</th>
                                <th class="px-6 py-3 text-center">Balita</th>
                                <th class="px-6 py-3 text-center">Ditimbang</th>
                                <th class="px-6 py-3 text-center">Perlu perhatian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($posyanduComparison as $item)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ $item['name'] }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ $item['familiesCount'] }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ $item['childrenCount'] }}</td>
                                    <td class="px-6 py-4 text-center text-gray-600">{{ $item['measuredCount'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-semibold {{ $item['riskCount'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                                            {{ $item['riskCount'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-8 text-center text-gray-500" colspan="5">Belum ada data Posyandu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden border-y border-gray-200 bg-white sm:rounded-lg sm:border">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <h3 class="font-semibold text-gray-900">Balita Perlu Perhatian</h3>
                    <p class="text-sm text-gray-500">Status gizi kurang atau buruk berdasarkan pengukuran terakhir bulan ini</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr><th class="px-6 py-3">Balita</th><th class="px-6 py-3">Posyandu</th><th class="px-6 py-3">Tanggal</th><th class="px-6 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($riskChildren as $measurement)
                                <tr>
                                    <td class="px-6 py-4"><a class="font-medium text-blue-700 hover:underline" href="{{ route('children.show', $measurement->child) }}">{{ $measurement->child->name }}</a><p class="text-xs text-gray-500">Ibu {{ $measurement->child->parent?->mother_name ?? '-' }}</p></td>
                                    <td class="px-6 py-4 text-gray-600">{{ $measurement->child->posyandu?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($measurement->measurement_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-4 font-semibold {{ \App\Models\Measurement::normalizeStatus($measurement->status) === \App\Models\Measurement::STATUS_SEVERE_UNDERWEIGHT ? 'text-red-700' : 'text-amber-700' }}">{{ \App\Models\Measurement::normalizeStatus($measurement->status) }}</td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-8 text-center text-gray-500" colspan="4">Tidak ada balita berstatus gizi kurang atau buruk bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Gizi Baik', 'Gizi Kurang', 'Gizi Buruk', 'Risiko Berat Lebih', 'Belum Terklasifikasi'],
                    datasets: [{
                        data: @json(array_values($statusSummary)),
                        backgroundColor: ['#16a34a', '#d97706', '#dc2626', '#2563eb', '#9ca3af'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: @json($trend['labels']),
                    datasets: [
                        { label: 'Gizi Baik', data: @json($trend['normal']), borderColor: '#16a34a', backgroundColor: '#16a34a', tension: 0.25 },
                        { label: 'Gizi Kurang', data: @json($trend['underweight']), borderColor: '#d97706', backgroundColor: '#d97706', tension: 0.25 },
                        { label: 'Gizi Buruk', data: @json($trend['severeUnderweight']), borderColor: '#dc2626', backgroundColor: '#dc2626', tension: 0.25 },
                        { label: 'Risiko Berat Lebih', data: @json($trend['overweightRisk']), borderColor: '#2563eb', backgroundColor: '#2563eb', tension: 0.25 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        });
    </script>
</x-app-layout>
