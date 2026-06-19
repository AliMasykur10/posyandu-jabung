<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Dashboard Orang Tua</h2>
            <p class="text-sm text-gray-500">Perkembangan kesehatan anak Anda</p>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
            @forelse ($childrenData as $index => $data)
                @php
                    $child = $data['child'];
                    $latest = $data['latest'];
                    $status = $data['normalizedStatus'];
                    $years = intdiv($data['ageMonths'], 12);
                    $months = $data['ageMonths'] % 12;
                    $statusColor = match ($status) {
                        \App\Models\Measurement::STATUS_NORMAL => 'text-green-700 bg-green-50',
                        \App\Models\Measurement::STATUS_UNDERWEIGHT => 'text-amber-700 bg-amber-50',
                        \App\Models\Measurement::STATUS_SEVERE_UNDERWEIGHT => 'text-red-700 bg-red-50',
                        \App\Models\Measurement::STATUS_OVERWEIGHT_RISK => 'text-blue-700 bg-blue-50',
                        default => 'text-gray-700 bg-gray-100',
                    };
                @endphp

                <section class="overflow-hidden border-y border-gray-200 bg-white sm:rounded-lg sm:border sm:shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $child->name }}</h3>
                            <p class="text-sm text-gray-500">Usia {{ $years > 0 ? $years . ' tahun ' : '' }}{{ $months }} bulan</p>
                        </div>
                        <span class="w-fit rounded-md px-3 py-1 text-sm font-semibold {{ $statusColor }}">{{ $status ?? 'Belum ada status gizi' }}</span>
                    </div>

                    <div class="grid grid-cols-2 border-b border-gray-200 sm:grid-cols-4">
                        <div class="border-b border-r border-gray-200 p-4 sm:border-b-0 sm:p-5"><p class="text-xs font-medium uppercase text-gray-500">Timbang Terakhir</p><p class="mt-1 font-semibold text-gray-900">{{ $latest ? \Carbon\Carbon::parse($latest->measurement_date)->translatedFormat('d M Y') : '-' }}</p></div>
                        <div class="border-b border-gray-200 p-4 sm:border-b-0 sm:border-r sm:p-5"><p class="text-xs font-medium uppercase text-gray-500">Berat Badan</p><p class="mt-1 font-semibold text-gray-900">{{ $latest ? $latest->weight . ' kg' : '-' }}</p></div>
                        <div class="border-r border-gray-200 p-4 sm:p-5"><p class="text-xs font-medium uppercase text-gray-500">Tinggi Badan</p><p class="mt-1 font-semibold text-gray-900">{{ $latest ? $latest->height . ' cm' : '-' }}</p></div>
                        <div class="p-4 sm:p-5"><p class="text-xs font-medium uppercase text-gray-500">Berat Lahir</p><p class="mt-1 font-semibold text-gray-900">{{ $child->birth_weight }} kg</p></div>
                    </div>

                    @if ($latest)
                        <div class="grid grid-cols-1 gap-6 p-4 sm:p-6 lg:grid-cols-3">
                            <div class="lg:col-span-2">
                                <h4 class="font-semibold text-gray-900">Grafik Pertumbuhan</h4>
                                <p class="mb-4 text-sm text-gray-500">Berat dan tinggi berdasarkan riwayat penimbangan</p>
                                <div class="h-72"><canvas id="childChart{{ $index }}"></canvas></div>
                            </div>
                            <div class="border-t border-gray-200 pt-5 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                                <h4 class="font-semibold text-gray-900">Layanan Terakhir</h4>
                                <dl class="mt-4 space-y-4 text-sm">
                                    <div><dt class="text-gray-500">Vitamin A</dt><dd class="font-medium text-gray-900">{{ $latest->vitamin_a ?: 'Belum dicatat' }}</dd></div>
                                    <div><dt class="text-gray-500">Obat cacing</dt><dd class="font-medium text-gray-900">{{ $latest->deworming_medicine ? 'Sudah diberikan' : 'Belum diberikan' }}</dd></div>
                                    <div><dt class="text-gray-500">PMT</dt><dd class="font-medium text-gray-900">{{ $latest->pmt_status ?: 'Belum dicatat' }}</dd></div>
                                    <div><dt class="text-gray-500">Catatan kesehatan</dt><dd class="font-medium text-gray-900">{{ $latest->notes ?: 'Tidak ada catatan' }}</dd></div>
                                </dl>
                                <a class="mt-6 inline-block text-sm font-semibold text-blue-700 hover:underline" href="{{ route('children.show', $child) }}">Lihat riwayat lengkap</a>
                            </div>
                        </div>
                    @else
                        <div class="px-4 py-10 text-center sm:px-6"><p class="font-medium text-gray-700">Belum ada data penimbangan</p><p class="mt-1 text-sm text-gray-500">Data pertumbuhan akan tampil setelah kader mencatat pengukuran pertama.</p></div>
                    @endif
                </section>
            @empty
                <section class="border-y border-gray-200 bg-white px-4 py-12 text-center sm:rounded-lg sm:border sm:px-6">
                    <h3 class="font-semibold text-gray-900">Belum ada data anak</h3>
                    <p class="mt-1 text-sm text-gray-500">Hubungi kader Posyandu untuk menghubungkan data anak dengan akun Anda.</p>
                </section>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const charts = @json($childrenData->pluck('chart')->values());
            charts.forEach((data, index) => {
                const canvas = document.getElementById(`childChart${index}`);
                if (!canvas) return;
                new Chart(canvas, {
                    type: 'line',
                    data: { labels: data.labels, datasets: [
                        { label: 'Berat (kg)', data: data.weights, borderColor: '#2563eb', backgroundColor: '#2563eb', yAxisID: 'weight', tension: 0.25 },
                        { label: 'Tinggi (cm)', data: data.heights, borderColor: '#16a34a', backgroundColor: '#16a34a', yAxisID: 'height', tension: 0.25 }
                    ] },
                    options: { responsive: true, maintainAspectRatio: false, scales: {
                        weight: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'kg' } },
                        height: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'cm' } }
                    } }
                });
            });
        });
    </script>
</x-app-layout>
