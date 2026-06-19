<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Orang Tua') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h3 class="mb-4 text-lg font-bold">Daftar Anak Anda:</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @forelse($myChildren as $child)
                    <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-2xl font-bold text-blue-600">{{ $child->name }}</h3>
                        <p class="text-gray-600">Tanggal Lahir: {{ $child->birth_date }}</p>

                        <div class="mt-4 border-t pt-4">
                            <h4 class="font-semibold">Riwayat Kesehatan Terakhir:</h4>
                            @if ($child->measurements && $child->measurements->count() > 0)
                                @php $last = $child->measurements->last(); @endphp
                                <p>Berat Badan: {{ $last->weight }} kg</p>
                                <p>Status Gizi:
                                    @if ($child->measurements->isNotEmpty())
                                        <span
                                            class="{{ $child->measurements->last()->status == 'Gizi Baik (Normal)' ? 'text-green-600' : 'text-red-600' }} font-bold">
                                            {{ $child->measurements->last()->status }}
                                        </span>
                                    @else
                                        <span class="italic text-gray-400">Belum ada data</span>
                                    @endif
                                </p>
                            @else
                                <p class="italic text-gray-400">Belum ada data penimbangan.</p>
                            @endif
                        </div>

                        <div class="mt-6">

                            <a class="font-medium text-blue-500 hover:underline"
                                href="{{ route('children.show', $child->id) }}">

                                Lihat Grafik Lengkap &raquo;

                            </a>


                        </div>
                    </div>
                @empty
                    <p>Tidak ada data anak yang ditemukan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
