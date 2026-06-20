<nav class="border-b border-gray-100 bg-white" x-data="{ open: false }">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">

            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :active="request()->routeIs('dashboard')" :href="route('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @canany(['is-admin', 'is-kader'])
                        <x-nav-link :active="request()->routeIs('children.*')" :href="route('children.index')">
                            {{ __('Data Balita') }}
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('measurements.*')" :href="route('measurements.index')">
                            {{ __('Catat Timbangan') }}
                        </x-nav-link>

                        <x-nav-link :active="request()->routeIs('parents.*')" :href="route('parents.index')">
                            {{ __('Orang Tua') }}
                        </x-nav-link>
                    @endcanany
                </div>
            </div>

            <!-- Settings Dropdown Desktop -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        fill-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Mobile -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" d="M6 18L18 6M6 6l12 12"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu Mobile -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden border-t border-gray-100 bg-white sm:hidden">

        <div class="space-y-1 px-2 pb-3 pt-2">
            <a class="{{ request()->routeIs('dashboard')
                ? 'border-indigo-400 bg-indigo-50 text-indigo-700'
                : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} block border-l-4 px-4 py-3 text-left text-base font-medium"
                href="{{ route('dashboard') }}">
                Dashboard
            </a>

            @canany(['is-admin', 'is-kader'])
                <a class="{{ request()->routeIs('children.*')
                    ? 'border-indigo-400 bg-indigo-50 text-indigo-700'
                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} block border-l-4 px-4 py-3 text-left text-base font-medium"
                    href="{{ route('children.index') }}">
                    Data Balita
                </a>

                <a class="{{ request()->routeIs('measurements.*')
                    ? 'border-indigo-400 bg-indigo-50 text-indigo-700'
                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} block border-l-4 px-4 py-3 text-left text-base font-medium"
                    href="{{ route('measurements.index') }}">
                    Catat Timbangan
                </a>

                <a class="{{ request()->routeIs('parents.*')
                    ? 'border-indigo-400 bg-indigo-50 text-indigo-700'
                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} block border-l-4 px-4 py-3 text-left text-base font-medium"
                    href="{{ route('parents.index') }}">
                    Orang Tua
                </a>
            @endcanany
        </div>

        <div class="border-t border-gray-200 px-4 pb-3 pt-4">
            <div class="text-base font-semibold text-gray-800">
                {{ Auth::user()->name }}
            </div>
            <div class="text-sm text-gray-500">
                {{ Auth::user()->email }}
            </div>
        </div>

        <div class="space-y-1 px-2 pb-3">
            <a class="block border-l-4 border-transparent px-4 py-3 text-left text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800"
                href="{{ route('profile.edit') }}">
                Profile
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button
                    class="block w-full border-l-4 border-transparent px-4 py-3 text-left text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800"
                    type="submit">
                    Log Out
                </button>
            </form>
        </div>

    </div>
</nav>
