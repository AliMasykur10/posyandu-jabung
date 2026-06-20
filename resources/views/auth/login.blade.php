<x-guest-layout>
    <div class="mb-8">
        <p class="text-sm font-semibold text-teal-700">Selamat datang</p>
        <h2 class="mt-2 text-2xl font-bold text-gray-900">Masuk ke akun Anda</h2>
        <p class="mt-2 text-sm leading-6 text-gray-500">
            Gunakan email dan kata sandi yang telah terdaftar.
        </p>
    </div>

    <x-auth-session-status class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" :status="session('status')" />

    <form action="{{ route('login') }}" method="POST" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat email" />
            <x-text-input
                autocomplete="username"
                autofocus
                class="mt-2 block min-w-0 w-full max-w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-teal-600 focus:ring-teal-600"
                id="email"
                name="email"
                placeholder="nama@contoh.id"
                required
                type="email"
                :value="old('email')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="mt-5">
            <x-input-label for="password" value="Kata sandi" />

            <div class="relative mt-2 min-w-0 w-full max-w-full">
                <x-text-input
                    autocomplete="current-password"
                    class="block min-w-0 w-full max-w-full rounded-md border-gray-300 px-3 py-2.5 pe-12 shadow-sm focus:border-teal-600 focus:ring-teal-600"
                    id="password"
                    name="password"
                    required
                    x-bind:type="showPassword ? 'text' : 'password'"
                />

                <button
                    class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-gray-500 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-600"
                    type="button"
                    x-bind:aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    x-on:click="showPassword = ! showPassword"
                >
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" x-show="! showPassword">
                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                        <circle cx="12" cy="12" r="2.75" />
                    </svg>
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" x-cloak x-show="showPassword">
                        <path d="m4 4 16 16M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.2 2.9M7.4 7.4C4.3 9.1 2.5 12 2.5 12s3.5 6 9.5 6c1.2 0 2.3-.2 3.3-.6M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                    </svg>
                </button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div class="mt-5 flex items-center">
            <input
                class="h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600"
                id="remember_me"
                name="remember"
                type="checkbox"
            >
            <label class="ms-2 text-sm text-gray-600" for="remember_me">Ingat saya</label>
        </div>

        <button
            class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md border border-transparent bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
            type="submit"
        >
            Masuk
        </button>

        <div class="mt-6 border-t border-gray-200 pt-5 text-center">
            <p class="text-sm text-gray-500">
                Lupa kata sandi? Hubungi admin atau kader Posyandu Anda.
            </p>
        </div>
    </form>
</x-guest-layout>
