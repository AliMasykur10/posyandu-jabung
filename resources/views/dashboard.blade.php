<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
</x-app-layout>