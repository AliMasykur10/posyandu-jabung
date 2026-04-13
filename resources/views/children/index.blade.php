<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Children Data - Jabung Sisir') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="mb-4 flex justify-between">
                    <h3 class="text-lg font-bold">List of Children</h3>
                    <div class="mb-4 flex justify-between">
                        <a class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-blue-900"
                            href="{{ route('children.create') }}">
                            Add Child
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <table class="min-w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="w-16 border p-2 text-center">No</th>
                            <th class="border p-2">Child Name</th>
                            <th class="border p-2">Posyandu Unit</th>
                            <th class="border p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($children as $index => $child)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2 text-center">{{ $index + 1 }}</td>
                                <td class="border p-2">{{ $child->name }}</td>
                                <td class="border p-2">{{ $child->posyandu->name_posyandu }}</td>
                                <td class="border p-2 text-center">
                                    <a class="text-green-600 hover:underline"
                                        href="{{ route('children.edit', $child->id) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border p-2 text-center italic text-gray-500" colspan="4">
                                    No children data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
