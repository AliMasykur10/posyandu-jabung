<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta content="{{ csrf_token() }}" name="csrf-token">

        <title>{{ config('app.name', 'Posyandu Jabung Sisir') }} | Masuk</title>

        <link href="https://fonts.bunny.net" rel="preconnect">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="flex min-h-screen w-full max-w-full items-center justify-center overflow-x-hidden px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid min-w-0 w-full max-w-5xl grid-cols-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg lg:min-h-[620px] lg:grid-cols-[0.9fr_1.1fr]">
                <section class="flex min-w-0 flex-col justify-between bg-teal-800 p-6 text-white sm:p-8 lg:p-12">
                    <div>
                        <a class="inline-flex items-center gap-3 rounded-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-800" href="/">
                            <x-application-logo class="h-12 w-12 text-white" />
                            <span class="text-sm font-semibold uppercase">Posyandu Jabung Sisir</span>
                        </a>

                        <div class="mt-6 max-w-sm lg:mt-20">
                            <p class="text-sm font-semibold text-teal-100">Sistem Informasi Posyandu</p>
                            <h1 class="mt-2 text-2xl font-bold leading-tight sm:text-3xl lg:mt-3 lg:text-4xl">
                                Pelayanan kesehatan balita dalam satu sistem.
                            </h1>
                            <p class="mt-3 text-sm leading-6 text-teal-100 lg:mt-4 lg:text-base lg:leading-7">
                                Desa Jabung Sisir, Kecamatan Paiton, Kabupaten Probolinggo.
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 border-t border-teal-700 pt-4 text-sm text-teal-100 lg:mt-12 lg:pt-5">
                        Akses khusus pengguna yang telah terdaftar.
                    </p>
                </section>

                <section class="flex min-w-0 items-center px-6 py-7 sm:px-10 lg:px-14 lg:py-8">
                    <div class="mx-auto min-w-0 w-full max-w-md">
                        {{ $slot }}
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
