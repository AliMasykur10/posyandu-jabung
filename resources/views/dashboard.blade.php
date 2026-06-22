<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $dashboardTitle }}</h2>
                <p class="text-sm text-gray-500">Pemantauan {{ $periodLabel }}</p>
            </div>
            @if ($isKader)
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800" href="{{ route('measurements.index') }}">Catat Penimbangan</a>
                    <a class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" href="{{ route('children.index') }}">Tambah Balita</a>
                </div>
            @endif
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 px-4 sm:grid-cols-2 sm:px-0 lg:grid-cols-4">
                @if ($isKader)
                    @php
                        $cards = [
                            ['label' => 'Balita Terdaftar', 'value' => $totalChildren, 'color' => 'text-gray-900'],
                            ['label' => 'Keluarga Terdaftar', 'value' => $totalFamilies, 'color' => 'text-gray-900'],
                            ['label' => 'Sudah Ditimbang', 'value' => $measuredChildren, 'color' => 'text-green-700'],
                            ['label' => 'Belum Ditimbang', 'value' => $unmeasuredCount, 'color' => 'text-amber-700'],
                        ];
                    @endphp
                @else
                    @php
                        $cards = [
                            ['label' => 'Balita Terdaftar', 'value' => $totalChildren, 'color' => 'text-gray-900'],
                            ['label' => 'Ditimbang Bulan Ini', 'value' => $measuredChildren, 'color' => 'text-green-700'],
                            ['label' => 'BB Kurang / Sangat Kurang', 'value' => $statusSummary['underweight'] + $statusSummary['severeUnderweight'], 'color' => 'text-red-700'],
                            ['label' => 'Risiko BB Lebih', 'value' => $statusSummary['overweightRisk'], 'color' => 'text-blue-700'],
                        ];
                    @endphp
                @endif

                @foreach ($cards as $card)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="border-y border-gray-200 bg-white px-4 py-5 sm:rounded-lg sm:border sm:px-6">
                <div class="flex items-end justify-between gap-4">
                    <div><h3 class="font-semibold text-gray-900">Cakupan Penimbangan</h3><p class="text-sm text-gray-500">{{ $measuredChildren }} dari {{ $totalChildren }} balita pada {{ $periodLabel }}</p></div>
                    <span class="text-2xl font-semibold text-blue-700">{{ $coveragePercentage }}%</span>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded bg-gray-200"><div class="h-full bg-blue-600" style="width: {{ min($coveragePercentage, 100) }}%"></div></div>
            </section>

            <section class="grid grid-cols-1 gap-6 px-4 sm:px-0 lg:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h3 class="font-semibold text-gray-900">Distribusi Status Berat Badan (BB/U)</h3>
                    <p class="mb-4 text-sm text-gray-500">Satu pengukuran terbaru per balita</p>
                    <div class="mx-auto h-72 max-w-sm"><canvas id="statusChart"></canvas></div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm lg:col-span-3">
                    <h3 class="font-semibold text-gray-900">Tren Enam Bulan</h3>
                    <p class="mb-4 text-sm text-gray-500">Perubahan jumlah balita berdasarkan status berat badan</p>
                    <div class="h-72"><canvas id="trendChart"></canvas></div>
                </div>
            </section>

            @unless ($isKader)
                <section class="overflow-hidden border-y border-gray-200 bg-white sm:rounded-lg sm:border">
                    <div class="border-b border-gray-200 px-4 py-4 sm:px-6"><h3 class="font-semibold text-gray-900">Perbandingan Antar-Posyandu</h3></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-6 py-3">Posyandu</th><th class="px-6 py-3 text-center">Balita</th><th class="px-6 py-3 text-center">Ditimbang</th><th class="px-6 py-3 text-center">Perlu perhatian</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($posyanduComparison as $item)
                                    <tr><td class="px-6 py-4 font-medium text-gray-900">{{ $item['name'] }}</td><td class="px-6 py-4 text-center">{{ $item['childrenCount'] }}</td><td class="px-6 py-4 text-center">{{ $item['measuredCount'] }}</td><td class="px-6 py-4 text-center font-semibold {{ $item['riskCount'] ? 'text-red-700' : 'text-green-700' }}">{{ $item['riskCount'] }}</td></tr>
                                @empty
                                    <tr><td class="px-6 py-8 text-center text-gray-500" colspan="4">Belum ada data Posyandu.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endunless

            <section class="overflow-hidden border-y border-gray-200 bg-white sm:rounded-lg sm:border">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6"><h3 class="font-semibold text-gray-900">Balita Perlu Perhatian</h3><p class="text-sm text-gray-500">Berat badan kurang atau sangat kurang pada pengukuran terakhir bulan ini</p></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><tr><th class="px-6 py-3">Balita</th><th class="px-6 py-3">Posyandu</th><th class="px-6 py-3">Pengukuran</th><th class="px-6 py-3">Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($riskChildren as $measurement)
                                <tr><td class="px-6 py-4"><a class="font-medium text-blue-700 hover:underline" href="{{ route('children.show', $measurement->child) }}">{{ $measurement->child->name }}</a><p class="text-xs text-gray-500">Ibu {{ $measurement->child->parent?->mother_name ?? '-' }}</p></td><td class="px-6 py-4 text-gray-600">{{ $measurement->child->posyandu?->name ?? '-' }}</td><td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($measurement->measurement_date)->translatedFormat('d M Y') }}</td><td class="px-6 py-4 font-semibold {{ \App\Models\Measurement::normalizeStatus($measurement->status) === \App\Models\Measurement::STATUS_SEVERE_UNDERWEIGHT ? 'text-red-700' : 'text-amber-700' }}">{{ \App\Models\Measurement::normalizeStatus($measurement->status) }}</td></tr>
                            @empty
                                <tr><td class="px-6 py-8 text-center text-gray-500" colspan="4">Tidak ada balita dengan berat badan kurang atau sangat kurang bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($isKader)
                <section class="grid grid-cols-1 gap-6 px-4 sm:px-0 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-5 py-4"><h3 class="font-semibold text-gray-900">Belum Ditimbang Bulan Ini</h3></div>
                        <ul class="divide-y divide-gray-100">
                            @forelse ($unmeasuredChildren as $child)
                                <li class="flex items-center justify-between gap-4 px-5 py-3"><div><a class="font-medium text-gray-900 hover:text-blue-700" href="{{ route('children.show', $child) }}">{{ $child->name }}</a><p class="text-xs text-gray-500">Ibu {{ $child->parent?->mother_name ?? '-' }}</p></div><span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($child->birth_date)->age }} tahun</span></li>
                            @empty
                                <li class="px-5 py-8 text-center text-sm text-gray-500">Semua balita sudah ditimbang bulan ini.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-5 py-4"><h3 class="font-semibold text-gray-900">Pencatatan Terbaru</h3></div>
                        <ul class="divide-y divide-gray-100">
                            @forelse ($recentMeasurements as $measurement)
                                <li class="flex items-center justify-between gap-4 px-5 py-3"><div><p class="font-medium text-gray-900">{{ $measurement->child->name }}</p><p class="text-xs text-gray-500">{{ $measurement->weight }} kg / {{ $measurement->height }} cm</p></div><div class="text-right"><p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($measurement->measurement_date)->format('d/m/Y') }}</p><p class="text-xs font-medium text-gray-700">{{ \App\Models\Measurement::normalizeStatus($measurement->status) ?? 'Belum terklasifikasi' }}</p></div></li>
                            @empty
                                <li class="px-5 py-8 text-center text-sm text-gray-500">Belum ada pencatatan.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: { labels: ['BB Normal', 'BB Kurang', 'BB Sangat Kurang', 'Risiko BB Lebih', 'Belum Terklasifikasi'], datasets: [{ data: @json(array_values($statusSummary)), backgroundColor: ['#16a34a', '#d97706', '#dc2626', '#2563eb', '#9ca3af'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: { labels: @json($trend['labels']), datasets: [
                    { label: 'BB Normal', data: @json($trend['normal']), borderColor: '#16a34a', backgroundColor: '#16a34a', tension: 0.25 },
                    { label: 'BB Kurang', data: @json($trend['underweight']), borderColor: '#d97706', backgroundColor: '#d97706', tension: 0.25 },
                    { label: 'BB Sangat Kurang', data: @json($trend['severeUnderweight']), borderColor: '#dc2626', backgroundColor: '#dc2626', tension: 0.25 },
                    { label: 'Risiko BB Lebih', data: @json($trend['overweightRisk']), borderColor: '#2563eb', backgroundColor: '#2563eb', tension: 0.25 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        });
    </script>
</x-app-layout>
