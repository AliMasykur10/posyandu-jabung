<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Add New Child</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form action="{{ route('children.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Child Name</label>
                        <input class="w-full rounded-md border-gray-300 shadow-sm" name="name" required
                            type="text">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Birth Date</label>
                        <input class="w-full rounded-md border-gray-300 shadow-sm" name="birth_date" required
                            type="date">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <select class="w-full rounded-md border-gray-300 shadow-sm" name="gender">
                            <option value="L">Laki-laki (Male)</option>
                            <option value="P">Perempuan (Female)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Posyandu Unit</label>
                        <select class="w-full rounded-md border-gray-300 shadow-sm" name="posyandu_id">
                            @foreach ($posyandus as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_posyandu }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button class="rounded bg-blue-600 px-4 py-2 text-white" type="submit">Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
